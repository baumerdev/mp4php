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

/**
 * Chunk Large Offset Box (type 'co64')
 *
 * aligned(8) class ChunkOffsetBox extends FullBox(‘co64’, version = 0, 0) {
 *     unsigned int(32) entry_count;
 *     for (i=1; i <= entry_count; i++) {
 *         unsigned int(64) chunk_offset;
 *     }
 * }
 */
class ChunkLargeOffsetBox extends ChunkOffsetBox
{
    const TYPE = 'co64';

    /**
     * Unpack 64-bit entry size
     *
     * @return int[]
     */
    protected function unpackEntrySize(): array
    {
        return unpack('JentrySize', $this->readHandle->read(8));
    }

    /**
     * Write entries as 64-bit uint
     */
    protected function writeEntries(): void
    {
        foreach ($this->entries as $entry) {
            $this->writeHandle->write(pack('J', $entry));
        }
    }
}
