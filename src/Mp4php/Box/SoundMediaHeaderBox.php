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
use Mp4php\DataType\Integer;
use Mp4php\Exceptions\ParserException;

/**
 * Sound Media Header Box (type 'smhd')
 *
 * aligned(8) class SoundMediaHeaderBox
 *     extends FullBox(‘smhd’, version = 0, 0) {
 *     template int(16) balance = 0;
 *     const unsigned int(16) reserved = 0;
 * }
 */
class SoundMediaHeaderBox extends AbstractMediaHeaderBox
{
    const TYPE = 'smhd';

    protected $boxImmutable = false;

    /**
     * @var FixedPoint 8.8
     */
    protected $balance;

    public function getBalance(): FixedPoint
    {
        return $this->balance;
    }

    public function setBalance(FixedPoint $balance): void
    {
        $this->balance = $balance;
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpackFormat = 'nbalance/nreserved';
        $read = $this->readHandle->read(4);
        if ($unpacked = unpack($unpackFormat, $read)) {
            $this->balance = FixedPoint::createFromInt(Integer::unsignedIntToSignedInt($unpacked['balance'], 16), 8, 8);
        } else {
            throw new ParserException('Cannot parse Sound Media Header Box.');
        }
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        // Balance
        $this->writeHandle->write(pack('n', Integer::signedIntToUnsignedInt($this->balance->toInt(), 16)));

        // Reserved
        $this->writeHandle->write(pack('n', 0));
    }
}
