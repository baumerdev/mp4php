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

use Mp4php\DataType\FontRecord;
use Mp4php\Exceptions\ParserException;

/**
 * Font Table Box (type 'ftab')
 *
 * class FontTableBox() extends Box(‘ftab’) {
 *   unsigned int(16) entry-count;
 *   FontRecord font-entry[entry-count];
 * }
 *
 * @see https://developer.apple.com/library/content/documentation/QuickTime/QTFF/QTFFChap3/qtff3.html#//apple_ref/doc/uid/TP40000939-CH205-SW80
 */
class FontTableBox extends Box
{
    const TYPE = 'ftab';

    protected $boxImmutable = false;

    /**
     * @var FontRecord[]
     */
    protected $fontRecords = [];

    /**
     * @return FontRecord[]
     */
    public function getFontRecords(): array
    {
        return $this->fontRecords;
    }

    /**
     * @param FontRecord[] $fontRecords
     */
    public function setFontRecords(array $fontRecords): void
    {
        $this->fontRecords = $fontRecords;
        $this->setModified();
    }

    /**
     * Parse the Subtitle Sample Entry Box
     */
    protected function parse(): void
    {
        $unpacked = unpack('nfontCount', $this->readHandle->read(2));
        if (!$unpacked) {
            throw new ParserException('Cannot parse font count.');
        }

        $fontCount = $unpacked['fontCount'];

        while ($this->readHandle->offset() < ($this->offset + $this->size)) {
            $unpacked = unpack('nfontIdentifier/CfontLength', $this->readHandle->read(3));
            if (!$unpacked) {
                throw new ParserException('Cannot parse font identifier and length.');
            }

            $fontLength = $unpacked['fontLength'];

            $fontRecord = new FontRecord();
            $fontRecord->fontIdentifier = $unpacked['fontIdentifier'];
            $fontRecord->fontName = $this->readHandle->read($fontLength);

            $this->fontRecords[] = $fontRecord;
        }

        if (\count($this->fontRecords) !== $fontCount) {
            throw new ParserException(sprintf('Expected %d font records but got %d.', $fontCount, \count($this->fontRecords)));
        }
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        // Font record count
        $this->writeHandle->write(pack('n', \count($this->fontRecords)));

        // Font records
        foreach ($this->fontRecords as $fontRecord) {
            $this->writeHandle->write(pack('n', $fontRecord->fontIdentifier));
            $this->writeHandle->write(pack('C', \strlen($fontRecord->fontName)));
            $this->writeHandle->write($fontRecord->fontName);
        }
    }
}
