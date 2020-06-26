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

use Mp4php\Exceptions\InvalidValueException;

/**
 * Helper Box for combining multiple Media Data Boxes into one (type 'mdat')
 *
 * In MP4 there is any number of 'mdat' boxes possible. When optimizing the file
 * for faststart option all of them are moved to the end of the file. Multiple
 * adjoined 'mdat' boxes can easily be grouped together in a single combined one.
 */
class MediaDataCombinedBox extends Box
{
    const BYTES_4GB = 4294967296;

    const TYPE = 'mdat';

    protected $container = [false];
    /**
     * @var array[]
     */
    protected $mdatData = [];

    /**
     * Add mdat info to this box
     *
     * @param MediaDataBox $box Media Data Box to use it's data from
     */
    public function addMediaDataBox(MediaDataBox $box): void
    {
        // If the box is empty, we can skip
        if ($box->size - $box->headerSize < 1) {
            return;
        }

        $this->mdatData[] = [
            'offsetMdatData' => $this->size - $this->headerSize,
            'offsetDataOrigin' => $box->offset + $box->headerSize,
            'sizeDataOrigin' => $box->size - $box->headerSize,
            'headerSizeDataOrigin' => $box->headerSize,
        ];

        $size = 8;
        foreach ($this->mdatData as $mdatData) {
            $size += $mdatData['sizeDataOrigin'];
        }
        if ($size >= static::BYTES_4GB) {
            $this->size = $size + 8;
            $this->headerSize = 16;
        } else {
            $this->size = $size;
            $this->headerSize = 8;
        }
    }

    /**
     * Calculate the new Offset of data based on the old file offset repositioned within this
     * box beginning at the current box's offset
     *
     * @param int $oldOffset      Offset of data in original file
     * @param int $sizeDifference Size difference (stco, co64) by which the offsets must be moved
     *
     * @return int|null
     */
    public function newOffset($oldOffset, $sizeDifference = 0)
    {
        foreach ($this->mdatData as $mdatData) {
            if ($oldOffset >= $mdatData['offsetDataOrigin'] &&
                $oldOffset <= (int) ($mdatData['offsetDataOrigin'] + $mdatData['sizeDataOrigin'])) {
                $offsetInOldBoxData = $oldOffset - $mdatData['offsetDataOrigin'];

                return $this->offset +
                    $this->headerSize +
                    $mdatData['offsetMdatData'] +
                    $offsetInOldBoxData +
                    $sizeDifference;
            }
        }

        return null;
    }

    /**
     * Just write null bytes with given length after header
     */
    public function write(?string $alternativeType = null): void
    {
        if ($alternativeType !== null && $alternativeType !== static::TYPE) {
            throw new InvalidValueException(sprintf('Box type must be "%s" but "%s" given.', static::TYPE, $alternativeType));
        }

        if ($this->headerSize === 16) {
            $this->writeHandle->write(pack('N', 1));
            $this->writeHandle->write(static::TYPE);
            $this->writeHandle->write(pack('J', $this->size));
        } else {
            $this->writeHandle->write(pack('N', $this->size));
            $this->writeHandle->write(static::TYPE);
        }

        foreach ($this->mdatData as $mdatData) {
            $this->writeHandle->copyData($this->readHandle, $mdatData['offsetDataOrigin'], $mdatData['sizeDataOrigin']);
        }
    }
}
