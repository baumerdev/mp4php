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
 * Abstract Track Reference Type Box (children of 'tref')
 *
 * aligned(8) class TrackReferenceBox extends Box(‘tref’) {
 * }
 * aligned(8) class TrackReferenceTypeBox (unsigned int(32) reference_type) extends
 *     Box(reference_type) {
 *     unsigned int(32) track_IDs[];
 * }
 */
abstract class AbstractTrackReferenceTypeBox extends Box
{
    protected $container = [TrackReferenceBox::class];

    /**
     * Referenced track IDs
     *
     * @var int[]
     */
    protected $trackIDs = [];

    /**
     * @return int[]
     */
    public function getTrackIDs(): array
    {
        return $this->trackIDs;
    }

    /**
     * @param int[] $trackIDs
     */
    public function setTrackIDs(array $trackIDs): void
    {
        if (array_diff($this->trackIDs, $trackIDs) === array_diff($trackIDs, $this->trackIDs)) {
            return;
        }

        $this->trackIDs = $trackIDs;
        $this->setModified();
    }

    /**
     * Parse box's track IDs
     */
    protected function parse(): void
    {
        $remaining = $this->remainingBytes();
        if ($remaining !== null && $remaining > 0) {
            $unpacked = unpack('N*', $this->readHandle->read($remaining));
            if ($unpacked) {
                $this->trackIDs = array_values($unpacked);
            } else {
                throw new ParserException('Cannot parse track IDs.');
            }
        }
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        foreach ($this->trackIDs as $trackID) {
            $this->writeHandle->write(pack('N', $trackID));
        }
    }
}
