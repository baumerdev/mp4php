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

use Mp4php\Exceptions\ParserException;
use Mp4php\Exceptions\SizeException;
use Mp4php\File\MP4ReadHandle;
use RuntimeException;

/**
 * Trait for handling some size and offset checks and modification
 */
trait BoxOffsetSizeTrait
{
    abstract protected function getReadHandle(): MP4ReadHandle;

    abstract protected function getSize(): int;

    abstract protected function getOffset(): int;

    /**
     * Get the box's start offset
     *
     * @param int $largeSize 64bit size if $size == 1
     *
     * @return int
     */
    protected function calculateBoxOffset($largeSize = null)
    {
        return $this->getReadHandle()->offset() - 8 - ($largeSize !== null ? 8 : 0);
    }

    /**
     * Calculate the size of the box (use parent offset if size = 0)
     *
     * @param int|null $largeSize 64bit size if $size == 1
     *
     * @throws RuntimeException
     * @throws ParserException
     */
    protected function boxSize(?int $size, ?int $largeSize = null): int
    {
        if ($size === null && $largeSize === null) {
            throw new ParserException('Either size (32bit) or largeSize (64bit) must be set.');
        }

        // Size zero means size to the end of file
        if ($size === 0) {
            $size = $this->getReadHandle()->size();

            if ($size < $this->getOffset()) {
                throw new ParserException(sprintf('File size %d is smaller than current offset %d.', $size, $this->getOffset()));
            }

            return $size - $this->getOffset();
        } elseif ($largeSize !== null) {
            return $largeSize;
        } else {
            return $size;
        }
    }

    /**
     * Return the remaining bytes in this box starting at handle's offset
     */
    protected function remainingBytes(): ?int
    {
        $remaining = ($this->getOffset() + $this->getSize()) - $this->getReadHandle()->offset();
        if ($remaining < 1) {
            return null;
        }

        return $remaining;
    }

    /**
     * Check if the offset after parsing is at the end of the box
     *
     * @throws SizeException
     */
    protected function offsetCheck(): void
    {
        $offset = $this->getOffset();
        $size = $this->getSize();
        $readHandleOffset = $this->getReadHandle()->offset();
        if ($offset + $size !== $readHandleOffset) {
            $class = static::class;

            throw new SizeException(sprintf('Offset of class "%s" should be at %d but is at %d.', $class, ($offset + $size), $readHandleOffset));
        }
    }
}
