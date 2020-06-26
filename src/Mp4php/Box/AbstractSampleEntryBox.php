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
 * Abstract class for Sample Entry Boxes
 *
 * aligned(8) abstract class SampleEntry (unsigned int(32) format)
 *     extends Box(format){
 *     const unsigned int(8)[6] reserved = 0;
 *     unsigned int(16) data_reference_index;
 * }
 */
abstract class AbstractSampleEntryBox extends Box
{
    protected $container = [SampleDescriptionBox::class];

    /**
     * @var int
     */
    protected $dataReferenceIndex;

    public function getDataReferenceIndex(): int
    {
        return $this->dataReferenceIndex;
    }

    /**
     * Parse the Sample Entry Box
     */
    protected function parse(): void
    {
        $unpacked = unpack('C6reserved/nreferenceIndex', $this->readHandle->read(8));
        if ($unpacked) {
            $this->dataReferenceIndex = $unpacked['referenceIndex'];
        } else {
            throw new ParserException('Cannot parse abstract Sample Entry');
        }
    }

    /**
     * Write 6 reserved null bytes and 2 bytes with dataReferenceIndex
     */
    protected function writeReservedReferenceIndex(): void
    {
        $this->writeHandle->write(pack('x6'));
        $this->writeHandle->write(pack('n', $this->dataReferenceIndex));
    }
}
