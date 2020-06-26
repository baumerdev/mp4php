<?php
/**
 * MP4PHP
 * PHP library for parsing and modifying MP4 files
 *
 * Copyright © 2016-2020 Markus Baumer <markus@baumer.dev>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 * the GNU General Public License for more details.
 */

declare(strict_types=1);

namespace Mp4php\Box;

use Exception;
use Mp4php\BoxBuilder;
use Mp4php\DataType\PropertyQuantity;
use Mp4php\Exceptions\BoxImmutableException;
use Mp4php\Exceptions\ConstructException;
use Mp4php\Exceptions\InvalidValueException;
use Mp4php\Exceptions\ParserException;
use Mp4php\File\MP4ReadHandle;
use Mp4php\File\MP4WriteHandle;
use RuntimeException;

/**
 * Default ISO BMFF Box
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Box
{
    use BoxOffsetSizeTrait;
    use BoxInfoTrait;

    /** Has to be overwritten by subclass if Box isn't created with explicit type name */
    const TYPE = null;

    /**
     * 4-char type of box, will be static::TYPE by default
     *
     * @var string
     */
    protected $type;
    /**
     * Box size in bytes including size information (4-12 bytes) and box type (4 bytes)
     *
     * @var int
     */
    protected $size;
    /**
     * Size of header 8 byte (size and type à 4 bytes) + optional 8 bytes for 64-bit size
     *
     * @var int
     */
    protected $headerSize;
    /**
     * Offset of the box from the start of the file
     *
     * @var int
     */
    protected $offset;
    /**
     * Children of this box that are not handled by indiviual properties
     *
     * @var Box[]|null
     */
    protected $children;
    /**
     * Handle (wrapper) for reading
     *
     * @var MP4ReadHandle
     */
    protected $readHandle;
    /**
     * Handle (wrapper) for writing
     *
     * @var MP4WriteHandle
     */
    protected $writeHandle;
    /**
     * Classes that are allowed to be parent for this box, false for root level
     *
     * @var array
     */
    protected $container = [];
    /**
     * Children boxes that should be used as property values
     * Checks if quantity is given
     *
     * @var array [className => [propertyName, PropertyQuantity::*]]
     */
    protected $classesProperties = [];
    /**
     * Keep track if box has changes that needs to be saved. Otherwise we may just copy bytes from start to end
     *
     * @var bool
     */
    protected $modified = false;
    /**
     * @var Box|null
     */
    protected $parent = null;
    /**
     * @var bool
     */
    protected $boxImmutable = true;

    /**
     * Construct Box
     */
    public function __construct(?self $parent = null, ?string $type = null)
    {
        $this->parent = $parent;
        $this->type = $type ?? static::TYPE;
        if ($this->type === null) {
            throw new ConstructException(sprintf('Class %s must be constructed with explicit $type.', static::class));
        }
        if (!preg_match('@^[a-zA-Z0-9 ©\-]{4}$@', $this->type)) {
            throw new ConstructException(sprintf('Type must be 4-char alphanumeric (spaces, - and © allowed) but was "%s".', $this->type));
        }
    }

    /**
     * Construct for parsing and parse the data in the box
     *
     * @param int|null $largeSize 64bit size if $size == 1
     * @param Box|null $parent    parent element
     *
     * @return Box
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function constructParse(MP4ReadHandle $readHandle, ?int $size = null, ?int $largeSize = null, ?self $parent = null)
    {
        if ($size === null && $largeSize === null) {
            throw new ConstructException('Either size (32bit) or largeSize (64bit) must be set.');
        }

        BoxValidation::withBox($this)->validateParentClass($parent, $this->container);

        $this->readHandle = $readHandle;
        $this->offset = $this->calculateBoxOffset($largeSize);
        $this->size = $this->boxSize($size, $largeSize);
        $this->headerSize = 8 + ($largeSize === null ? 0 : 8);
        $this->parent = $parent;

        $this->parse();
        $this->offsetCheck();

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return Box[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    /**
     * @param bool $modified
     */
    public function setModified($modified = true): void
    {
        if ($this->boxImmutable) {
            throw new BoxImmutableException(sprintf('Box "%s" cannot be modified.', static::class));
        }

        $this->modified = $modified;

        if ($this->parent) {
            $this->parent->setModified();
        }
    }

    /**
     * Set read handle (wrapper) to box and all child boxes (children and classesProperties)
     */
    public function setReadHandle(MP4ReadHandle $readHandle): void
    {
        $this->readHandle = $readHandle;

        $this->containingBoxesCallback(
            function (self $box) use ($readHandle): void {
                $box->setReadHandle($readHandle);
            }
        );
    }

    public function getReadHandle(): MP4ReadHandle
    {
        return $this->readHandle;
    }

    /**
     * Set write handle (wrapper) to box and all child boxes (children and classesProperties)
     */
    public function setWriteHandle(MP4WriteHandle $writeHandle): void
    {
        $this->writeHandle = $writeHandle;

        $this->containingBoxesCallback(
            function (self $box) use ($writeHandle): void {
                $box->setWriteHandle($writeHandle);
            }
        );
    }

    public function getWriteHandle(): MP4WriteHandle
    {
        return $this->writeHandle;
    }

    /**
     * Copy data from read to write or write new data if mutable
     *
     * It simply copies the data if not modified otherwise it calls write methods on children
     * and writes size to box
     */
    public function write(?string $alternativeType = null): void
    {
        if (!$this->isModified()) {
            $this->writeHandle->copyData($this->readHandle, $this->offset, $this->size);

            return;
        }

        if ($this->boxImmutable) {
            throw new BoxImmutableException(sprintf('Box %s cannot be modified.', static::class));
        }

        $beginOffset = $this->writeTypeGetBoxOffset($alternativeType);

        $this->writeModifiedContent();

        $this->updateSizeAtOffset($beginOffset);
    }

    /**
     * Remember offset, skips size (4 bytes), write type (4 bytes) and return remembered offset
     */
    public function writeTypeGetBoxOffset(?string $alternativeType = null): int
    {
        $type = $alternativeType ?? $this->type;

        $beginOffset = $this->writeHandle->offset();
        $this->writeHandle->seekBy(4);
        $newOffset = $this->writeHandle->offset();
        if ($beginOffset + 4 > $newOffset) {
            $this->writeHandle->write(str_repeat("\x00", $beginOffset + 4 - $newOffset));
        }
        if (\strlen($type) !== 4) {
            throw new InvalidValueException('Type must have a length of 4 chars.');
        }
        $this->writeHandle->write($type);

        return $beginOffset;
    }

    /**
     * Update Box size to size-bytes at beginOffset by subtracting beginOffset from current (end)offset
     *
     * @param int $beginOffset
     */
    public function updateSizeAtOffset($beginOffset): void
    {
        $endOffset = $this->writeHandle->offset() ?? 0;
        $this->writeHandle->seek($beginOffset);
        $this->writeHandle->write(pack('N', $endOffset - $beginOffset));
        $this->writeHandle->seek($endOffset);
    }

    /**
     * Count all children and classes property boxes
     *
     * @return int
     */
    public function containingBoxesCount()
    {
        $count = 0;

        $this->containingBoxesCallback(
            function () use (&$count): void {
                ++$count;
            }
        );

        return $count;
    }

    /**
     * Just write children; can be overwritten if sub-Box need individual content
     *
     * This method must be called from write() method because it does not prepend length and type nor
     * calculates the correct length afterwards!
     */
    protected function writeModifiedContent(): void
    {
        $this->writeChildren();
    }

    /**
     * Write all children and classes properties
     */
    protected function writeChildren(): void
    {
        $this->containingBoxesCallback(
            function (self $box): void {
                $box->write();
            }
        );
    }

    /**
     * Seek handle pointer to the end of the current box
     */
    protected function seekToBoxEnd(): void
    {
        $this->getReadHandle()->seek($this->getOffset() + $this->getSize());
    }

    /**
     * Parse the box's data: Just jump to the end if not overwritten,
     * because this Box has no logic to parse content if type is unknown
     * (which is the case if class is Box and not any subclass)
     */
    protected function parse(): void
    {
        $this->seekToBoxEnd();
    }

    /**
     * Parse the box's data/children
     */
    protected function parseChildren(): void
    {
        $this->resetClassProperties();

        $end = $this->offset + $this->size;

        while ($this->readHandle->offset() < $end) {
            $offsetBeforeBox = $this->readHandle->offset();
            try {
                $box = BoxBuilder::parsedBox($this->readHandle, $this);
                if ($box !== null && !$this->childAsProperty($box)) {
                    $this->addBoxToChildren($box);
                }
            } catch (Exception $e) {
                // Try to move to the end of the box to continue parsing even if something failed
                $this->readHandle->seek($offsetBeforeBox);
                [$type, $size, $largeSize] = $this->readHandle->readSizeType();
                $this->readHandle->seek($offsetBeforeBox + ($largeSize === null ? $size : $largeSize));

                // @todo: implement real logger
                printf(
                    "Failed parsing Box (type: '%s') at offset %d:\n%s\n\nTrace:\n%s",
                    $type,
                    $offsetBeforeBox,
                    $e->getMessage(),
                    $e->getTraceAsString()
                );
            }
        }

        BoxValidation::withBox($this)->validateClassProperties($this->classesProperties);
    }

    /**
     * Add child box to children array
     */
    protected function addBoxToChildren(self $box): void
    {
        if (!\is_array($this->children)) {
            $this->children = [$box];
        } else {
            $this->children[] = $box;
        }
    }

    /**
     * Perform callback on every Box in children and classes properties
     *
     * @param callable $callback function(Box $box)
     */
    protected function containingBoxesCallback(callable $callback): void
    {
        $this->containingClassPropertiesCallback($callback);
        $this->containingChildrenCallback($callback);
    }

    /**
     * Perform callback on every classes property box
     *
     * @param callable $callback function(Box $box)
     */
    protected function containingClassPropertiesCallback(callable $callback): void
    {
        if (\is_array($this->classesProperties)) {
            foreach ($this->classesProperties as $classProperty) {
                $propertyValues = $this->{$classProperty[0]};
                if (\is_array($propertyValues)) {
                    foreach ($propertyValues as $propertyValue) {
                        if (is_a($propertyValue, self::class)) {
                            $callback($propertyValue);
                        }
                    }
                } elseif (is_a($propertyValues, self::class)) {
                    $callback($propertyValues);
                }
            }
        }
    }

    /**
     * Perform callback on every child Box
     *
     * @param callable $callback function(Box $box)
     */
    protected function containingChildrenCallback(callable $callback): void
    {
        if (\is_array($this->children)) {
            foreach ($this->children as $child) {
                if (is_a($child, self::class)) {
                    $callback($child);
                }
            }
        }
    }

    /**
     * Reset class properties either as null or empty array as defined by its quantities
     */
    protected function resetClassProperties(): void
    {
        foreach ($this->classesProperties as $settings) {
            $property = $settings[0];
            $quantity = $settings[1];

            if (!property_exists($this, $property)) {
                throw new RuntimeException(sprintf('Property "%s" does not exist in class "%s"', $property, static::class));
            }

            if (\in_array($quantity, [PropertyQuantity::ONE, PropertyQuantity::ZERO_OR_ONE])) {
                $this->{$property} = null;
            } elseif (\in_array($quantity, [PropertyQuantity::ONE_OR_MORE, PropertyQuantity::ZERO_OR_MORE])) {
                $this->{$property} = [];
            }
        }
    }

    /**
     * Check if child should be handled as property and add it
     *
     * @return bool
     */
    protected function childAsProperty(self $child)
    {
        $classProperties = null;
        $childClass = \get_class($child);
        if (isset($this->classesProperties[$childClass])) {
            $classProperties = $this->classesProperties[$childClass];
        } else {
            foreach ($this->classesProperties as $class => $data) {
                if (is_subclass_of($childClass, $class)) {
                    $classProperties = $data;
                }
            }
        }

        if ($classProperties) {
            $property = $classProperties[0];
            $quantity = $classProperties[1];

            if (\in_array($quantity, [PropertyQuantity::ONE, PropertyQuantity::ZERO_OR_ONE])) {
                if ($this->{$property} !== null) {
                    throw new ParserException("Property $property can only contain one value.");
                }
                $this->{$property} = $child;
            } elseif (\in_array($quantity, [PropertyQuantity::ONE_OR_MORE, PropertyQuantity::ZERO_OR_MORE])) {
                $this->{$property}[] = $child;
            }

            return true;
        }

        return false;
    }
}
