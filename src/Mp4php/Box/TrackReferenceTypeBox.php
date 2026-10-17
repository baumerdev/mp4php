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
use Override;

/**
 * Track Reference Type Box (multiple types, child to Track Reference Box)
 *
 * aligned(8) class TrackReferenceTypeBox (unsigned int(32) reference_type) extends
 *     Box(reference_type) {
 *     unsigned int(32) track_IDs[];
 * }
 */
class TrackReferenceTypeBox extends AbstractTrackReferenceTypeBox
{
    public const string TYPE_FORC = 'forc';

    protected bool $boxImmutable = false;

    protected array $container = [TrackReferenceBox::class];

    /**
     * @var int[]
     */
    protected array $trackIds = [];

    /**
     * @return int[]
     */
    #[Override]
    public function getTrackIds(): array
    {
        return $this->trackIds;
    }

    /**
     * @param int[] $trackIds
     */
    #[Override]
    public function setTrackIds(array $trackIds): void
    {
        if (array_diff($trackIds, $this->trackIds) === array_diff($this->trackIds, $trackIds)) {
            return;
        }

        $this->trackIds = $trackIds;
        $this->setModified();
    }

    /**
     * Parse track IDs
     */
    #[Override]
    protected function parse(): void
    {
        $remainingBytes = $this->remainingBytes();
        $unpacked = unpack('N*', $this->readHandle->read($remainingBytes));
        if ($unpacked) {
            $this->trackIds = array_values($unpacked);
        } else {
            throw new ParserException('Cannot parse track IDs.');
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function writeModifiedContent(): void
    {
        foreach ($this->trackIds as $trackId) {
            $this->writeHandle->write(pack('N', $trackId));
        }
    }
}
