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
 * Sample Size Box (type 'stsz')
 *
 * aligned(8) class SampleSizeBox extends FullBox(‘stsz’, version = 0, 0) {
 *     unsigned int(32) sample_size;
 *     unsigned int(32) sample_count;
 *     if (sample_size==0) {
 *         for (i=1; i <= sample_count; i++) {
 *             unsigned int(32) entry_size;
 *         }
 *     }
 * }
 */
class SampleSizeBox extends AbstractFullBox
{
    const TYPE = 'stsz';

    protected $container = [SampleTableBox::class];

    /**
     * @var int
     */
    protected $sampleSize;
    /**
     * @var int
     */
    protected $sampleCount;
    /**
     * @var array|null [int]
     */
    protected $entries = null;
    /**
     * Offset the entries data start
     *
     * @var int
     */
    protected $entriesOffset;

    public function getSampleSize(): int
    {
        return $this->sampleSize;
    }

    public function getSampleCount(): int
    {
        return $this->sampleCount;
    }

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
        if ($this->sampleSize === 0) {
            for ($i = 1; $i <= $this->sampleCount; ++$i) {
                $unpacked = unpack('NentrySize', $this->readHandle->read(4));
                if (!$unpacked) {
                    throw new ParserException('Cannot parse entry.');
                }
                $this->entries[] = $unpacked['entrySize'];
            }
        }

        return $this->entries;
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('NsampleSize/NsampleCount', $this->readHandle->read(8));
        if (!$unpacked) {
            throw new ParserException('Cannot parse sample size/count.');
        }
        $this->sampleSize = $unpacked['sampleSize'];
        $this->sampleCount = $unpacked['sampleCount'];

        $this->entriesOffset = $this->readHandle->offset();

        $this->seekToBoxEnd();
    }
}
