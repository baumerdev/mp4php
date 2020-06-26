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

namespace Mp4php\Box\Codec;

use Mp4php\Box\Box;
use Mp4php\Exceptions\ParserException;

/**
 * Box for E-AC3 codec details (type 'dec3')
 */
class CodecDEC3Box extends Box
{
    const TYPE = 'dec3';

    /**
     * Datarate
     *
     * @var int
     */
    protected $datarate;

    /**
     * @var string
     */
    protected $eac3SpecificBox;

    /**
     * Atmos Version, 0 if none
     *
     * @var int
     */
    protected $atmosVersion;

    /**
     * Atmos object count
     *
     * @var int|null
     */
    protected $atmosObjectCount;

    public function getDatarate(): int
    {
        return $this->datarate;
    }

    public function setDatarate(int $datarate): void
    {
        $this->datarate = $datarate;

        $this->setModified();
    }

    public function getAtmosVersion(): int
    {
        return $this->atmosVersion;
    }

    public function setAtmosVersion(int $atmosVersion): void
    {
        $this->atmosVersion = $atmosVersion;

        $this->setModified();
    }

    public function getAtmosObjectCount(): ?int
    {
        return $this->atmosObjectCount;
    }

    public function setAtmosObjectCount(?int $atmosObjectCount): void
    {
        $this->atmosObjectCount = $atmosObjectCount;

        $this->setModified();
    }

    /**
     * Parse box's channel and subwoofer info
     */
    public function parse(): void
    {
        $flags = sprintf('%016d', decbin(hexdec(bin2hex($this->readHandle->read(2)))));
        $this->datarate = bindec(substr($flags, 0, 13));
        $indSubstreams = bindec(substr($flags, 13, 3));

        if ($indSubstreams > 0) {
            throw new ParserException('[dec3] Unable to parse if number of independent streams is greater 0.');
        }

        $this->eac3SpecificBox = $this->readHandle->read(3);

        $this->atmosVersion = hexdec(bin2hex($this->readHandle->read(1)));

        if ($this->atmosVersion === 1) {
            $this->atmosObjectCount = hexdec(bin2hex($this->readHandle->read(1))) - 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(hex2bin(dechex(bindec(sprintf('%013d%03d', decbin($this->datarate), 0)))));
        $this->writeHandle->write($this->eac3SpecificBox);
        $this->writeHandle->write(hex2bin(sprintf('%02x', dechex($this->atmosVersion))));
        if ($this->atmosVersion === 1) {
            $this->writeHandle->write(hex2bin(sprintf('%02x', ($this->atmosObjectCount + 1))));
        }
    }
}
