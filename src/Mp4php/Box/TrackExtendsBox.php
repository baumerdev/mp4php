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
 * Track Extends Box (type 'trex')
 *
 * aligned(8) class TrackExtendsBox extends FullBox(‘trex’, 0, 0){
 *     unsigned int(32) track_ID;
 *     unsigned int(32) default_sample_description_index;
 *     unsigned int(32) default_sample_duration;
 *     unsigned int(32) default_sample_size;
 *     unsigned int(32) default_sample_flags
 * }
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackExtendsBox extends AbstractFullBox
{
    const TYPE = 'trex';

    protected $boxImmutable = false;

    protected $container = [MovieExtendsBox::class];
    /**
     * @var int
     */
    protected $trackID;
    /**
     * @var int
     */
    protected $defaultSampleDescriptionIndex;
    /**
     * @var int
     */
    protected $defaultSampleDuration;
    /**
     * @var int
     */
    protected $defaultSampleSize;
    /**
     * @var int
     */
    protected $defaultSampleFlags;

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

    public function getDefaultSampleDescriptionIndex(): int
    {
        return $this->defaultSampleDescriptionIndex;
    }

    public function setDefaultSampleDescriptionIndex(int $defaultSampleDescriptionIndex): void
    {
        if ($this->defaultSampleDescriptionIndex === $defaultSampleDescriptionIndex) {
            return;
        }

        $this->defaultSampleDescriptionIndex = $defaultSampleDescriptionIndex;
        $this->setModified();
    }

    public function getDefaultSampleDuration(): int
    {
        return $this->defaultSampleDuration;
    }

    public function setDefaultSampleDuration(int $defaultSampleDuration): void
    {
        if ($this->defaultSampleDuration === $defaultSampleDuration) {
            return;
        }

        $this->defaultSampleDuration = $defaultSampleDuration;
        $this->setModified();
    }

    public function getDefaultSampleSize(): int
    {
        return $this->defaultSampleSize;
    }

    public function setDefaultSampleSize(int $defaultSampleSize): void
    {
        if ($this->defaultSampleSize === $defaultSampleSize) {
            return;
        }

        $this->defaultSampleSize = $defaultSampleSize;
        $this->setModified();
    }

    public function getDefaultSampleFlags(): int
    {
        return $this->defaultSampleFlags;
    }

    public function setDefaultSampleFlags(int $defaultSampleFlags): void
    {
        if ($this->defaultSampleFlags === $defaultSampleFlags) {
            return;
        }

        $this->defaultSampleFlags = $defaultSampleFlags;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('Ntrack/Nindex/Nduration/Nsize/Nflags', $this->readHandle->read(20));
        if ($unpacked) {
            $this->trackID = $unpacked['track'];
            $this->defaultSampleDescriptionIndex = $unpacked['index'];
            $this->defaultSampleDuration = $unpacked['duration'];
            $this->defaultSampleSize = $unpacked['size'];
            $this->defaultSampleFlags = $unpacked['flags'];
        } else {
            throw new ParserException('Cannot parse box data.');
        }
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->trackID));
        $this->writeHandle->write(pack('N', $this->defaultSampleDescriptionIndex));
        $this->writeHandle->write(pack('N', $this->defaultSampleDuration));
        $this->writeHandle->write(pack('N', $this->defaultSampleSize));
        $this->writeHandle->write(pack('N', $this->defaultSampleFlags));
    }
}
