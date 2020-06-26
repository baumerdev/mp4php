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

use Mp4php\DataType\SampleChunk;
use Mp4php\Exceptions\ParserException;

/**
 * Sample To ChunkBox (type 'stsc')
 *
 * aligned(8) class SampleToChunkBox
 *     extends FullBox(‘stsc’, version = 0, 0) {
 *     unsigned int(32) entry_count;
 *     for (i=1; i <= entry_count; i++) {
 *         unsigned int(32) first_chunk;
 *         unsigned int(32) samples_per_chunk;
 *         unsigned int(32) sample_description_index;
 *     }
 * }
 */
class SampleToChunkBox extends AbstractFullBox
{
    const TYPE = 'stsc';

    protected $container = [SampleTableBox::class];

    /**
     * @var int
     */
    protected $entryCount;
    /**
     * @var SampleChunk[]|null
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
     * @return array [SampleChunk]
     */
    public function entries()
    {
        if ($this->entries !== null) {
            return $this->entries;
        }

        $this->readHandle->seek($this->entriesOffset);

        $this->entries = [];
        for ($i = 1; $i <= $this->entryCount; ++$i) {
            $unpackFormat = 'Nfirst/Nsample/Ndescription';
            $read = $this->readHandle->read(12);
            if ($unpacked = unpack($unpackFormat, $read)) {
                $this->entries[] = new SampleChunk($unpacked['first'], $unpacked['sample'], $unpacked['description']);
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

        $unpacked = unpack('NentryCount', $this->readHandle->read(4));
        if (!$unpacked) {
            throw new ParserException('Cannot parse entry count.');
        }
        $this->entryCount = $unpacked['entryCount'];

        $this->entriesOffset = $this->readHandle->offset();

        $this->seekToBoxEnd();
    }
}
