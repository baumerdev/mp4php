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

use Mp4php\DataType\FixedPoint;
use Mp4php\DataType\PropertyQuantity;
use Mp4php\Exceptions\ParserException;

/**
 * Visual Sample Entry Box (stsd entry for handlerType vide)
 *
 * class VisualSampleEntry(codingname) extends SampleEntry (codingname){
 *     unsigned int(16) pre_defined = 0;
 *     const unsigned int(16) reserved = 0;
 *     unsigned int(32)[3] pre_defined = 0;
 *     unsigned int(16) width;
 *     unsigned int(16) height;
 *     template unsigned int(32) horizresolution = 0x00480000; // 72 dpi
 *     template unsigned int(32) vertresolution = 0x00480000; // 72 dpi
 *     const unsigned int(32) reserved = 0;
 *     template unsigned int(16) frame_count = 1;
 *     string[32] compressorname;
 *     template unsigned int(16) depth = 0x0018;
 *     int(16) pre_defined = -1;
 *     // other boxes from derived specifications
 *     CleanApertureBox clap; // optional
 *     PixelAspectRatioBox pasp; // optional
 * }
 *
 * @todo CleanApertureBox
 */
class VisualSampleEntryBox extends AbstractSampleEntryBox
{
    protected $boxImmutable = false;

    protected $classesProperties = [
        PixelAspectRatioBox::class => ['pixelAspectRatio', PropertyQuantity::ZERO_OR_ONE],
        ElementaryStreamDescriptorBox::class => ['esDescriptor', PropertyQuantity::ZERO_OR_ONE],
    ];

    /**
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $height;
    /**
     * @var FixedPoint 16.16
     */
    protected $horizontalResolution;
    /**
     * @var FixedPoint 16.16
     */
    protected $verticalResolution;
    /**
     * @var int
     */
    protected $frameCount;
    /**
     * @var string
     */
    protected $compressorName;
    /**
     * @var int
     */
    protected $depth;
    /**
     * @var PixelAspectRatioBox|null
     */
    protected $pixelAspectRatio;
    /**
     * @var ElementaryStreamDescriptorBox|null
     */
    protected $esDescriptor;

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): void
    {
        if ($this->width === $width) {
            return;
        }

        $this->width = $width;
        $this->setModified();
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): void
    {
        if ($this->height === $height) {
            return;
        }

        $this->height = $height;
        $this->setModified();
    }

    public function getHorizontalResolution(): FixedPoint
    {
        return $this->horizontalResolution;
    }

    public function setHorizontalResolution(FixedPoint $horizontalResolution): void
    {
        if ((string) $this->horizontalResolution === (string) $horizontalResolution) {
            return;
        }

        $this->horizontalResolution = $horizontalResolution;
        $this->setModified();
    }

    public function getVerticalResolution(): FixedPoint
    {
        return $this->verticalResolution;
    }

    public function setVerticalResolution(FixedPoint $verticalResolution): void
    {
        if ((string) $this->verticalResolution === (string) $verticalResolution) {
            return;
        }

        $this->verticalResolution = $verticalResolution;
        $this->setModified();
    }

    public function getFrameCount(): int
    {
        return $this->frameCount;
    }

    public function setFrameCount(int $frameCount): void
    {
        if ($this->frameCount === $frameCount) {
            return;
        }

        $this->frameCount = $frameCount;
        $this->setModified();
    }

    public function getCompressorName(): string
    {
        return $this->compressorName;
    }

    public function setCompressorName(string $compressorName): void
    {
        if ($this->compressorName === $compressorName) {
            return;
        }

        $this->compressorName = $compressorName;
        $this->setModified();
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): void
    {
        if ($this->depth === $depth) {
            return;
        }

        $this->depth = $depth;
        $this->setModified();
    }

    public function getPixelAspectRatio(): ?PixelAspectRatioBox
    {
        return $this->pixelAspectRatio;
    }

    public function setPixelAspectRatio(?PixelAspectRatioBox $pixelAspectRatio): void
    {
        $this->pixelAspectRatio = $pixelAspectRatio;
        $this->setModified();
    }

    public function getEsDescriptor(): ?ElementaryStreamDescriptorBox
    {
        return $this->esDescriptor;
    }

    public function setEsDescriptor(?ElementaryStreamDescriptorBox $esDescriptor): void
    {
        $this->esDescriptor = $esDescriptor;
        $this->setModified();
    }

    /**
     * Parse the Audio Sample Entry Box
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack(
            'npredef/nreserved/N3predef/nwidth/nheight/Nhres/Nvres/Nreserved/nframeCount/H64compressor/ndepth/npredef',
            $this->readHandle->read(70)
        );
        if ($unpacked) {
            $this->width = $unpacked['width'];
            $this->height = $unpacked['height'];
            $this->horizontalResolution = FixedPoint::createFromInt($unpacked['hres'], 16, 16);
            $this->verticalResolution = FixedPoint::createFromInt($unpacked['vres'], 16, 16);
            $this->frameCount = $unpacked['frameCount'];
            $this->compressorName = (string) hex2bin($unpacked['compressor']);
            $this->depth = $unpacked['depth'];
        } else {
            throw new ParserException('Cannot parse Audio Sample Entry Box');
        }

        $this->parseChildren();
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        $this->writeReservedReferenceIndex();

        // Pre defined & reserved
        $this->writeHandle->write(pack('n', 0));
        $this->writeHandle->write(pack('n', 0));
        $this->writeHandle->write(pack('N', 0));
        $this->writeHandle->write(pack('N', 0));
        $this->writeHandle->write(pack('N', 0));

        // Width
        $this->writeHandle->write(pack('n', $this->width));

        // Height
        $this->writeHandle->write(pack('n', $this->height));

        // H-Res
        $this->writeHandle->write(pack('N', $this->horizontalResolution->toInt()));

        // V-Res
        $this->writeHandle->write(pack('N', $this->verticalResolution->toInt()));

        // Reserved
        $this->writeHandle->write(pack('N', 0));

        // Frame count
        $this->writeHandle->write(pack('n', $this->frameCount));

        // Compressor
        $this->writeHandle->write(str_pad($this->compressorName, 32));

        // Depth
        $this->writeHandle->write(pack('n', $this->depth));

        // Pre defined
        $this->writeHandle->write(pack('n', 0));

        // Children
        parent::writeModifiedContent();
    }
}
