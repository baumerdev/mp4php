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
 * Track Fragment Header Box (type 'tfhd')
 *
 * aligned(8) class TrackFragmentHeaderBox
 *     extends FullBox(‘tfhd’, 0, tf_flags){
 *     unsigned int(32) track_ID;
 *     // all the following are optional fields
 *     unsigned int(64) base_data_offset;
 *     unsigned int(32) sample_description_index;
 *     unsigned int(32) default_sample_duration;
 *     unsigned int(32) default_sample_size;
 *     unsigned int(32) default_sample_flags
 * }
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackFragmentHeaderBox extends AbstractFullBox
{
    const TYPE = 'tfhd';

    const FLAG_BASE_DATA_OFFSET_PRESENT = 1;
    const FLAG_SAMPLE_DESCRIPTION_INDEX_PRESENT = 2;
    const FLAG_DEFAULT_SAMPLE_DURATION_PRESENT = 8;
    const FLAG_DEFAULT_SAMPLE_SIZE_PRESENT = 16;
    const FLAG_DEFAULT_SAMPLE_FLAGS_PRESENT = 32;
    const FLAG_DURATION_IS_EMPTY = 65536;
    const FLAG_DEFAULT_BASE_IS_MOOF = 1048576;

    protected $container = [TrackFragmentBox::class];

    /**
     * @var bool
     */
    protected $baseDataOffsetPresent;
    /**
     * @var bool
     */
    protected $sampleDescriptionIndexPresent;
    /**
     * @var bool
     */
    protected $defaultSampleDurationPresent;
    /**
     * @var bool
     */
    protected $defaultSampleSizePresent;
    /**
     * @var bool
     */
    protected $defaultSampleFlagsPresent;
    /**
     * @var bool
     */
    protected $durationIsEmpty;
    /**
     * @var bool
     */
    protected $defaultBaseIsMOOF;
    /**
     * @var int
     */
    protected $trackID;
    /**
     * @var int
     */
    protected $baseDataOffset;
    /**
     * @var int
     */
    protected $sampleDescriptionIndex;
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

    public function isBaseDataOffsetPresent(): bool
    {
        return $this->baseDataOffsetPresent;
    }

    public function isSampleDescriptionIndexPresent(): bool
    {
        return $this->sampleDescriptionIndexPresent;
    }

    public function isDefaultSampleDurationPresent(): bool
    {
        return $this->defaultSampleDurationPresent;
    }

    public function isDefaultSampleSizePresent(): bool
    {
        return $this->defaultSampleSizePresent;
    }

    public function isDefaultSampleFlagsPresent(): bool
    {
        return $this->defaultSampleFlagsPresent;
    }

    public function isDurationIsEmpty(): bool
    {
        return $this->durationIsEmpty;
    }

    public function isDefaultBaseIsMOOF(): bool
    {
        return $this->defaultBaseIsMOOF;
    }

    public function getTrackID(): int
    {
        return $this->trackID;
    }

    public function getBaseDataOffset(): int
    {
        return $this->baseDataOffset;
    }

    public function getSampleDescriptionIndex(): int
    {
        return $this->sampleDescriptionIndex;
    }

    public function getDefaultSampleDuration(): int
    {
        return $this->defaultSampleDuration;
    }

    public function getDefaultSampleSize(): int
    {
        return $this->defaultSampleSize;
    }

    public function getDefaultSampleFlags(): int
    {
        return $this->defaultSampleFlags;
    }

    /**
     * Parse box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $this->parseFlags();

        $unpacked = unpack('Ntrack', $this->readHandle->read(4));
        if ($unpacked) {
            $this->trackID = $unpacked['track'];
        } else {
            throw new ParserException('Cannot parse track ID.');
        }

        [$unpackString, $bytes] = $this->unpackElements();
        $unpacked = unpack($unpackString, $this->readHandle->read($bytes));
        if (!$unpacked) {
            throw new ParserException('Cannot parse optional box data.');
        }
        $this->unpackedToProperties($unpacked);
    }

    /**
     * Parse flag params
     */
    protected function parseFlags(): void
    {
        $this->baseDataOffsetPresent = $this->checkFlag(self::FLAG_BASE_DATA_OFFSET_PRESENT);
        $this->sampleDescriptionIndexPresent = $this->checkFlag(self::FLAG_SAMPLE_DESCRIPTION_INDEX_PRESENT);
        $this->defaultSampleDurationPresent = $this->checkFlag(self::FLAG_DEFAULT_SAMPLE_DURATION_PRESENT);
        $this->defaultSampleSizePresent = $this->checkFlag(self::FLAG_DEFAULT_SAMPLE_SIZE_PRESENT);
        $this->defaultSampleFlagsPresent = $this->checkFlag(self::FLAG_DEFAULT_SAMPLE_FLAGS_PRESENT);
        $this->durationIsEmpty = $this->checkFlag(self::FLAG_DURATION_IS_EMPTY);
        $this->defaultBaseIsMOOF = $this->checkFlag(self::FLAG_DEFAULT_BASE_IS_MOOF);
    }

    /**
     * Unpack string and total size to read
     *
     * @return array [unpack string, bytes]
     */
    protected function unpackElements()
    {
        $unpackElements = [];
        $bytes = 0;
        if ($this->baseDataOffsetPresent) {
            $unpackElements[] = 'Joffset';
            $bytes += 8;
        }
        if ($this->sampleDescriptionIndexPresent) {
            $unpackElements[] = 'Ndescription';
            $bytes += 4;
        }
        if ($this->defaultSampleDurationPresent) {
            $unpackElements[] = 'Nduration';
            $bytes += 4;
        }
        if ($this->defaultSampleSizePresent) {
            $unpackElements[] = 'Nsize';
            $bytes += 4;
        }
        if ($this->defaultSampleFlagsPresent) {
            $unpackElements[] = 'Nflags';
            $bytes += 4;
        }

        return [implode('/', $unpackElements), $bytes];
    }

    /**
     * Create new SampleDurationSizeFlagsOffset from unpack data
     *
     * @param array $unpacked
     */
    protected function unpackedToProperties($unpacked): void
    {
        if (isset($unpacked['offset'])) {
            $this->baseDataOffset = $unpacked['offset'];
        }
        if (isset($unpacked['description'])) {
            $this->sampleDescriptionIndex = $unpacked['description'];
        }
        if (isset($unpacked['duration'])) {
            $this->defaultSampleDuration = $unpacked['duration'];
        }
        if (isset($unpacked['size'])) {
            $this->defaultSampleSize = $unpacked['size'];
        }
        if (isset($unpacked['flags'])) {
            $this->defaultSampleFlags = $unpacked['flags'];
        }
    }
}
