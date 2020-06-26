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

/**
 * Pixel Aspect Ratio Box (type 'pasp')
 *
 * class PixelAspectRatioBox extends Box(‘pasp’){
 *     unsigned int(32) hSpacing;
 *     unsigned int(32) vSpacing;
 * }
 */
class PixelAspectRatioBox extends Box
{
    const TYPE = 'pasp';

    protected $boxImmutable = false;

    protected $container = [VisualSampleEntryBox::class];

    /**
     * @var int
     */
    protected $horizontalSpacing;
    /**
     * @var int
     */
    protected $verticalSpacing;

    public function getHorizontalSpacing(): int
    {
        return $this->horizontalSpacing;
    }

    public function setHorizontalSpacing(int $horizontalSpacing): void
    {
        if ($this->horizontalSpacing === $horizontalSpacing) {
            return;
        }

        $this->horizontalSpacing = $horizontalSpacing;
        $this->setModified();
    }

    public function getVerticalSpacing(): int
    {
        return $this->verticalSpacing;
    }

    public function setVerticalSpacing(int $verticalSpacing): void
    {
        if ($this->verticalSpacing === $verticalSpacing) {
            return;
        }

        $this->verticalSpacing = $verticalSpacing;
        $this->setModified();
    }

    /**
     * Parse the file types box's data
     */
    protected function parse(): void
    {
        if ($unpacked = unpack('NhSpacing/NvSpacing', $this->readHandle->read(8))) {
            $this->horizontalSpacing = $unpacked['hSpacing'];
            $this->verticalSpacing = $unpacked['vSpacing'];
        } else {
            throw new ParserException('Cannot parse minorVersion');
        }
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->horizontalSpacing));
        $this->writeHandle->write(pack('N', $this->verticalSpacing));
    }
}
