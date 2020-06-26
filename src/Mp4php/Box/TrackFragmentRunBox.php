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

use Mp4php\DataType\Integer;
use Mp4php\DataType\SampleDurationSizeFlagsOffset;
use Mp4php\Exceptions\ParserException;

/**
 * Track Fragment Run Box (type 'trun')
 *
 * aligned(8) class TrackRunBox extends FullBox(‘trun’, version, tr_flags) {
 * *     unsigned int(32) sample_count;
 *     // the following are optional fields
 *     signed int(32) data_offset;
 *     unsigned int(32) first_sample_flags;
 *     // all fields in the following array are optional
 *     {
 *         unsigned int(32) sample_duration;
 *         unsigned int(32) sample_size;
 *         unsigned int(32) sample_flags
 *         if (version == 0) {
 *             unsigned int(32) sample_composition_time_offset;
 *         } else {
 *             signed int(32) sample_composition_time_offset;
 *         }
 *     }[ sample_count ]
 * }
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackFragmentRunBox extends AbstractFullBox
{
    const TYPE = 'trun';

    const FLAG_DATA_OFFSET_PRESENT = 1;
    const FLAG_FIRST_SAMPLE_FLAGS_PRESENT = 4;
    const FLAG_SAMPLE_DURATION_PRESENT = 256;
    const FLAG_SAMPLE_SIZE_PRESENT = 512;
    const FLAG_SAMPLE_FLAGS_PRESENT = 1024;
    const FLAG_SAMPLE_OFFSET_PRESENT = 2048;

    protected $container = [TrackFragmentBox::class];

    /**
     * @var bool
     */
    protected $dataOffsetPresent;
    /**
     * @var bool
     */
    protected $firstSampleFlagsPresent;
    /**
     * @var bool
     */
    protected $sampleDurationPresent;
    /**
     * @var bool
     */
    protected $sampleSizePresent;
    /**
     * @var bool
     */
    protected $sampleFlagsPresent;
    /**
     * @var bool
     */
    protected $sampleOffsetPresent;
    /**
     * @var int
     */
    protected $sampleCount;
    /**
     * @var int
     */
    protected $dataOffset;
    /**
     * @var int
     */
    protected $firstSampleFlags;
    /**
     * @var array|null [SampleDurationSizeFlagsOffset]
     */
    protected $entries;
    /**
     * Offset the entries data start
     *
     * @var int
     */
    protected $entriesOffset;

    /**
     * Return array of entries (either cached from var or freshly parsed)
     *
     * @return array [int]
     */
    public function entries()
    {
        if ($this->entries !== null) {
            return $this->entries;
        }

        $this->readHandle->seek($this->entriesOffset);

        $this->entries = [];
        [$unpackString, $bytes] = $this->unpackElements();
        for ($i = 1; $i <= $this->sampleCount; ++$i) {
            $unpacked = unpack($unpackString, $this->readHandle->read($bytes));
            if (!$unpacked) {
                throw new ParserException('Cannot parse sample entry.');
            }
            $this->entries[] = $this->unpackedToEntry($unpacked);
        }

        return $this->entries;
    }

    /**
     * Parse box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $this->parseFlags();

        $unpacked = unpack('NsampleCount', $this->readHandle->read(4));
        if (!$unpacked) {
            throw new ParserException('Cannot parse sample count.');
        }
        $this->sampleCount = $unpacked['sampleCount'];

        $this->parseOptionalProperties();

        if ($this->sampleDurationPresent || $this->sampleSizePresent || $this->sampleFlagsPresent || $this->sampleOffsetPresent) {
            $this->entriesOffset = $this->readHandle->offset();
        } else {
            $this->entries = [];
        }

        $this->seekToBoxEnd();
    }

    /**
     * Parse flag params
     */
    protected function parseFlags(): void
    {
        $this->dataOffsetPresent = $this->checkFlag(self::FLAG_DATA_OFFSET_PRESENT);
        $this->firstSampleFlagsPresent = $this->checkFlag(self::FLAG_FIRST_SAMPLE_FLAGS_PRESENT);
        $this->sampleDurationPresent = $this->checkFlag(self::FLAG_SAMPLE_DURATION_PRESENT);
        $this->sampleSizePresent = $this->checkFlag(self::FLAG_SAMPLE_SIZE_PRESENT);
        $this->sampleFlagsPresent = $this->checkFlag(self::FLAG_SAMPLE_FLAGS_PRESENT);
        $this->sampleOffsetPresent = $this->checkFlag(self::FLAG_SAMPLE_OFFSET_PRESENT);
    }

    /**
     * Parse optional properties base on flags
     */
    protected function parseOptionalProperties(): void
    {
        if ($this->dataOffsetPresent) {
            $unpacked = unpack('Noffset', $this->readHandle->read(4));
            if (!$unpacked) {
                throw new ParserException('Cannot parse data offset.');
            }
            $this->dataOffset = Integer::unsignedIntToSignedInt($unpacked['offset'], 32);
        }

        if ($this->firstSampleFlagsPresent) {
            $unpacked = unpack('Nflags', $this->readHandle->read(4));
            if (!$unpacked) {
                throw new ParserException('Cannot parse first sample flags.');
            }
            $this->firstSampleFlags = $unpacked['flags'];
        }
    }

    /**
     * Unpack string and total size to read
     *
     * @return array [unpack string, bytes]
     */
    protected function unpackElements()
    {
        $unpackElements = [];
        if ($this->sampleDurationPresent) {
            $unpackElements[] = 'Nduration';
        }
        if ($this->sampleSizePresent) {
            $unpackElements[] = 'Nsize';
        }
        if ($this->sampleFlagsPresent) {
            $unpackElements[] = 'Nflags';
        }
        if ($this->sampleOffsetPresent) {
            $unpackElements[] = 'Noffset';
        }

        return [implode('/', $unpackElements), \count($unpackElements) * 4];
    }

    /**
     * Create new SampleDurationSizeFlagsOffset from unpack data
     *
     * @param array $unpacked
     *
     * @return SampleDurationSizeFlagsOffset
     */
    protected function unpackedToEntry($unpacked)
    {
        if ($this->version === 1 && isset($unpacked['offset'])) {
            $unpacked['offset'] = Integer::unsignedIntToSignedInt($unpacked['offset'], 32);
        }

        return new SampleDurationSizeFlagsOffset(
            $unpacked['duration'] ?? null,
            $unpacked['size'] ?? null,
            $unpacked['flags'] ?? null,
            $unpacked['offset'] ?? null
        );
    }
}
