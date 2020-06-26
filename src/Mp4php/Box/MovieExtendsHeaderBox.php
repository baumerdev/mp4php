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
 * Movie Extends Header Box (type 'mehd')
 *
 * aligned(8) class MovieExtendsHeaderBox extends FullBox(‘mehd’, version, 0) {
 *     if (version==1) {
 *         unsigned int(64) fragment_duration;
 *     } else { // version==0
 *         unsigned int(32) fragment_duration;
 *     }
 * }
 */
class MovieExtendsHeaderBox extends AbstractFullBox
{
    const TYPE = 'mehd';

    protected $boxImmutable = false;

    protected $container = [MovieExtendsBox::class];

    /**
     * @var int
     */
    protected $fragmentDuration;

    public function getFragmentDuration(): int
    {
        return $this->fragmentDuration;
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        if ($this->version === 1) {
            $unpacked = unpack('Jduration', $this->readHandle->read(8));
        } elseif ($this->version === 0) {
            $unpacked = unpack('Nduration', $this->readHandle->read(4));
        } else {
            throw new ParserException(sprintf('Version 0 or 1 expected but got %d', $this->version));
        }

        if ($unpacked) {
            $this->fragmentDuration = $unpacked['duration'];
        } else {
            throw new ParserException('Cannot parse fragment duration.');
        }
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        if ($this->version === 1) {
            $this->writeHandle->write(pack('J', $this->fragmentDuration));
        } elseif ($this->version === 0) {
            $this->writeHandle->write(pack('N', $this->fragmentDuration));
        } else {
            throw new ParserException(sprintf('Version 0 or 1 expected but got %d', $this->version));
        }
    }
}
