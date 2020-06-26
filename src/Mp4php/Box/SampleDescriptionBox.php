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

use Mp4php\BoxBuilder;
use Mp4php\Exceptions\ParserException;
use Mp4php\File\MP4ReadHandle;

/**
 * Sample Description Box (type 'stsd')
 */
class SampleDescriptionBox extends AbstractFullBox
{
    const TYPE = 'stsd';

    protected $boxImmutable = false;

    protected $container = [SampleTableBox::class];

    /**
     * @var string
     */
    protected $handlerType;

    public function getHandlerType(): string
    {
        return $this->handlerType;
    }

    public function setHandlerType(string $handlerType): void
    {
        if ($this->handlerType === $handlerType) {
            return;
        }

        $this->handlerType = $handlerType;
        $this->setModified();
    }

    /**
     * {@inheritdoc}
     */
    public function constructParse(MP4ReadHandle $readHandle, ?int $size = null, ?int $largeSize = null, ?Box $parent = null)
    {
        /** @var $parent SampleTableBox */
        if (!\is_object($parent) || !is_a($parent, SampleTableBox::class) || $parent->getHandlerType() === null) {
            throw new ParserException("Sample Table Box's handlerType needed for Sample Description Box.");
        }

        $this->handlerType = $parent->getHandlerType();

        return parent::constructParse($readHandle, $size, $largeSize, $parent);
    }

    /**
     * Parse the box's children
     *
     * TODO: Add HintSampleEntry, BitRateBox, XMLMetaDataSampleEntry, TextMetaDataSampleEntry,
     * TODO: Add URIMetaSampleEntry, PixelAspectRatioBox, CleanApertureBox, ColourInformationBox
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('NentryCount', $this->readHandle->read(4));
        if (!$unpacked) {
            throw new ParserException('Cannot parse entry count.');
        }
        $entryCount = $unpacked['entryCount'];

        for ($i = 1; $i <= $entryCount; ++$i) {
            [$type, $size, $largeSize] = $this->readHandle->readSizeType();

            if ($boxClass = BoxBuilder::classForType($type)) {
                /** @var Box $box */
                $box = new $boxClass($this);
            } elseif ($this->handlerType === HandlerReferenceBox::AUDIO_TRACK) {
                $box = new AudioSampleEntryBox($this, $type);
            } elseif ($this->handlerType === HandlerReferenceBox::VIDEO_TRACK) {
                $box = new VisualSampleEntryBox($this, $type);
            } elseif ($this->handlerType === HandlerReferenceBox::METADATA_TRACK) {
                $box = new MetaDataEntrySampleEntryBox($this, $type);
            } else {
                $box = new Box($this, $type);
            }

            $box->constructParse($this->readHandle, $size, $largeSize, $this);

            if (!\is_array($this->children)) {
                $this->children = [];
            }
            $this->children[] = $box;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        // Entry count
        if (\is_array($this->children)) {
            $this->writeHandle->write(pack('N', \count($this->children)));
        } else {
            $this->writeHandle->write(pack('N', 0));
        }

        $this->writeChildren();
    }
}
