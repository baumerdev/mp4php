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

use Mp4php\DataType\OPColor;
use Mp4php\Exceptions\ParserException;

/**
 * Video Media Header Box (type 'vmhd')
 *
 * aligned(8) class VideoMediaHeaderBox
 *     extends FullBox(‘vmhd’, version = 0, 1) {
 *     template unsigned int(16) graphicsmode = 0; // copy, see below
 *     template unsigned int(16)[3] opcolor = {0, 0, 0};
 * }
 */
class VideoMediaHeaderBox extends AbstractMediaHeaderBox
{
    const TYPE = 'vmhd';

    protected $boxImmutable = false;

    /**
     * @var int
     */
    protected $graphicsMode;
    /**
     * @var OPColor
     */
    protected $opcolor;

    public function getGraphicsMode(): int
    {
        return $this->graphicsMode;
    }

    public function setGraphicsMode(int $graphicsMode): void
    {
        if ($this->graphicsMode === $graphicsMode) {
            return;
        }

        $this->graphicsMode = $graphicsMode;
        $this->setModified();
    }

    public function getOpcolor(): OPColor
    {
        return $this->opcolor;
    }

    public function setOpcolor(OPColor $opcolor): void
    {
        if ((string) $this->opcolor === (string) $opcolor) {
            return;
        }

        $this->opcolor = $opcolor;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpackFormat = 'ngfxMode/n3opcolor';
        $read = $this->readHandle->read(8);
        if ($unpacked = unpack($unpackFormat, $read)) {
            $this->graphicsMode = $unpacked['gfxMode'];
            $this->opcolor = new OPColor($unpacked['opcolor1'], $unpacked['opcolor2'], $unpacked['opcolor3']);
        } else {
            throw new ParserException('Cannot parse Sound Media Header Box.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('n', $this->graphicsMode));

        $this->writeHandle->write(pack('n', $this->opcolor->red));
        $this->writeHandle->write(pack('n', $this->opcolor->green));
        $this->writeHandle->write(pack('n', $this->opcolor->blue));
    }
}
