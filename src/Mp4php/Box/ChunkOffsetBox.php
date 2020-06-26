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
 * Chunk Offset Box (type 'stco')
 *
 * aligned(8) class ChunkOffsetBox extends FullBox(‘stco’, version = 0, 0) {
 *     unsigned int(32) entry_count;
 *     for (i=1; i <= entry_count; i++) {
 *         unsigned int(32) chunk_offset;
 *     }
 * }
 */
class ChunkOffsetBox extends AbstractFullBox
{
    const TYPE = 'stco';

    protected $boxImmutable = false;

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
     * @var int
     */
    protected $entriesOffset;

    public function getEntryCount(): int
    {
        return $this->entryCount;
    }

    /**
     * Return array of entries (either cached from var or freshly parsed)
     *
     * Parsed only on demand because usually only needed for reordering (faststart)
     *
     * @return int[]
     */
    public function entries(): array
    {
        if ($this->entries !== null) {
            return $this->entries;
        }

        $this->readHandle->seek($this->entriesOffset);

        $this->entries = [];
        for ($i = 1; $i <= $this->entryCount; ++$i) {
            $unpacked = $this->unpackEntrySize();
            if (!$unpacked) {
                throw new ParserException('Cannot parse entry.');
            }
            $this->entries[] = $unpacked['entrySize'];
        }

        return $this->entries;
    }

    /**
     * Setter for entries (updates entryCount)
     *
     * @param int[] $entries
     */
    public function setEntries(array $entries): void
    {
        $this->entries = $entries;
        $this->entryCount = \count($entries);

        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        // FullBox
        parent::parse();

        $unpacked = unpack('NentryCount', $this->readHandle->read(4));
        if (!$unpacked) {
            throw new ParserException('Cannot parse entry count.');
        }
        $this->entryCount = $unpacked['entryCount'];

        $this->entriesOffset = $this->readHandle->offset();

        $this->seekToBoxEnd();
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->entryCount));
        $this->writeEntries();
    }

    /**
     * Unpack 32-bit entry size
     *
     * @return int[]
     */
    protected function unpackEntrySize(): array
    {
        return unpack('NentrySize', $this->readHandle->read(4));
    }

    /**
     * Write entries as 32-bit uint
     */
    protected function writeEntries(): void
    {
        if (!\is_array($this->entries)) {
            return;
        }

        foreach ($this->entries as $entry) {
            $this->writeHandle->write(pack('N', $entry));
        }
    }
}
