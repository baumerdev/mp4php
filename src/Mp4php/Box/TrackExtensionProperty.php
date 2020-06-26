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
 * Track Extension Property (type 'trep')
 *
 * class TrackExtensionPropertiesBox extends FullBox(‘trep’, 0, 0) {
 *     unsigned int(32) track_id;
 *     // Any number of boxes may follow
 * }
 */
class TrackExtensionProperty extends AbstractFullBox
{
    const TYPE = 'trep';

    protected $boxImmutable = false;

    protected $container = [MovieExtendsBox::class];

    /**
     * @var int
     */
    protected $trackID;

    public function getTrackID(): int
    {
        return $this->trackID;
    }

    public function setTrackID(int $trackID): void
    {
        if ($this->trackID === $trackID) {
            return;
        }

        $this->trackID = $trackID;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('Ntrack', $this->readHandle->read(4));
        if ($unpacked) {
            $this->trackID = $unpacked['track'];
        } else {
            throw new ParserException('Cannot parse track ID.');
        }

        $this->parseChildren();
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->trackID));
    }
}
