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
 * Sync Sample Box (type 'stss')
 *
 * aligned(8) class SyncSampleBox
 *     extends FullBox(‘stss’, version = 0, 0) {
 *     unsigned int(32) entry_count;
 *     int i;
 *     for (i=0; i < entry_count; i++) {
 *         unsigned int(32) sample_number;
 *     }
 * }
 */
class SyncSampleBox extends AbstractFullBox
{
    const TYPE = 'stss';

    protected $container = [SampleTableBox::class];

    /**
     * @var int
     */
    protected $entryCount;
    /**
     * @var int[]|null
     */
    protected $entries = null;
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
        for ($i = 1; $i <= $this->entryCount; ++$i) {
            $unpacked = unpack('NsampleNumber', $this->readHandle->read(4));
            if (!$unpacked) {
                throw new ParserException('Cannot parse entry.');
            }
            $this->entries[] = $unpacked['sampleNumber'];
        }

        return $this->entries;
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('NentryCount', $this->readHandle->read(4));
        if (!$unpacked) {
            throw new ParserException('Cannot parse entry count.');
        }
        $this->entryCount = $unpacked['entryCount'];

        $this->entriesOffset = $this->readHandle->offset();

        $this->seekToBoxEnd();
    }
}
