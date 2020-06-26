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
 * Track Reference Follow Subtitle (type 'folw')
 */
class TrackReferenceFollowSubtitle extends AbstractTrackReferenceTypeBox
{
    const TYPE = 'folw';

    protected $boxImmutable = false;

    /**
     * @var int
     */
    protected $followTrackID;

    public function getFollowTrackID(): int
    {
        return $this->followTrackID;
    }

    public function setFollowTrackID(int $followTrackID): void
    {
        if ($this->followTrackID === $followTrackID) {
            return;
        }

        $this->followTrackID = $followTrackID;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        $unpacked = unpack('Ntrack', $this->readHandle->read(4));
        if ($unpacked) {
            if (isset($unpacked['track'])) {
                $this->followTrackID = $unpacked['track'];
            }

            parent::parse();
        } else {
            throw new ParserException('Cannot parse chapter track ID.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->followTrackID));

        parent::writeModifiedContent();
    }
}
