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
 * Movie Fragment Header Box (type 'mfhd')
 *
 * aligned(8) class MovieFragmentHeaderBox
 *     extends FullBox(‘mfhd’, 0, 0){
 *     unsigned int(32) sequence_number;
 * }
 */
class MovieFragmentHeaderBox extends AbstractFullBox
{
    const TYPE = 'mfhd';

    protected $boxImmutable = false;

    protected $container = [MovieFragmentBox::class];

    /**
     * @var int
     */
    protected $sequenceNumber;

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function setSequenceNumber(int $sequenceNumber): void
    {
        if ($this->sequenceNumber === $sequenceNumber) {
            return;
        }

        $this->sequenceNumber = $sequenceNumber;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('Nsequence', $this->readHandle->read(4));
        if ($unpacked) {
            $this->sequenceNumber = $unpacked['sequence'];
        } else {
            throw new ParserException('Cannot parse sequence number.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->sequenceNumber));
    }
}
