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
 * File Type Box (type 'ftyp')
 *
 * aligned(8) class FileTypeBox
 *     extends Box(‘ftyp’) {
 *     unsigned int(32) major_brand;
 *     unsigned int(32) minor_version;
 *     unsigned int(32) compatible_brands[]; // to end of the box
 * }
 */
class FileTypeBox extends Box
{
    const TYPE = 'ftyp';

    protected $boxImmutable = false;

    protected $container = [false];

    /**
     * @var string
     */
    protected $majorBrand;
    /**
     * @var int
     */
    protected $minorVersion;
    /**
     * @var string[]
     */
    protected $compatibleBrands;

    public function getMajorBrand(): string
    {
        return $this->majorBrand;
    }

    public function setMajorBrand(string $majorBrand): void
    {
        if ($this->majorBrand === $majorBrand) {
            return;
        }

        $this->majorBrand = $majorBrand;
        $this->setModified();
    }

    public function getMinorVersion(): int
    {
        return $this->minorVersion;
    }

    public function setMinorVersion(int $minorVersion): void
    {
        if ($this->minorVersion === $minorVersion) {
            return;
        }

        $this->minorVersion = $minorVersion;
        $this->setModified();
    }

    /**
     * @return string[]
     */
    public function getCompatibleBrands(): array
    {
        return $this->compatibleBrands;
    }

    /**
     * @param string[] $compatibleBrands
     */
    public function setCompatibleBrands(array $compatibleBrands): void
    {
        $this->compatibleBrands = $compatibleBrands;
        $this->setModified();
    }

    /**
     * Parse the file types box's data
     */
    protected function parse(): void
    {
        // Major brand
        $this->majorBrand = $this->readHandle->read(4);

        // Minor version
        if ($unpacked = unpack('NminorVersion', $this->readHandle->read(4))) {
            $this->minorVersion = $unpacked['minorVersion'];
        } else {
            throw new ParserException('Cannot parse minorVersion');
        }

        // Compatible brands
        $compatibleBrands = [];
        for ($remainingSize = $this->size - 16; $remainingSize > 0; $remainingSize -= 4) {
            $compatibleBrands[] = $this->readHandle->read(4);
        }
        $this->compatibleBrands = $compatibleBrands;
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        // Major brand
        $this->writeHandle->write($this->majorBrand);

        // Minor version
        $this->writeHandle->write(pack('N', $this->minorVersion));

        // Compatible brands
        foreach ($this->compatibleBrands as $compatibleBrand) {
            $this->writeHandle->write(sprintf('%-4s', $compatibleBrand));
        }
    }
}
