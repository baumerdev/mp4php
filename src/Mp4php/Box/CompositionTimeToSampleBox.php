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
use Mp4php\DataType\SampleCountOffset;
use Mp4php\Exceptions\ParserException;

/**
 * Composition Time To Sample Box (type 'ctts')
 *
 * aligned(8) class CompositionOffsetBox
 *     extends FullBox(‘ctts’, version = 0, 0) {
 *     unsigned int(32) entry_count;
 *         int i;
 *     if (version==0) {
 *         for (i=0; i < entry_count; i++) {
 *             unsigned int(32) sample_count;
 *             unsigned int(32) sample_offset;
 *         }
 *     }
 *     else if (version == 1) {
 *         for (i=0; i < entry_count; i++) {
 *             unsigned int(32) sample_count;
 *             signed int(32) sample_offset;
 *         }
 *     }
 * }
 */
class CompositionTimeToSampleBox extends AbstractFullBox
{
    const TYPE = 'ctts';

    protected $container = [SampleTableBox::class];

    /**
     * @var int
     */
    protected $entryCount;
    /**
     * @var SampleCountOffset[]|null
     */
    protected $entries = null;
    /**
     * Offset the entries data start
     *
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
     * @return array SampleCountOffset[]
     */
    public function entries(): array
    {
        if ($this->entries !== null) {
            return $this->entries;
        }

        $this->readHandle->seek($this->entriesOffset);

        $this->entries = [];
        for ($i = 1; $i <= $this->entryCount; ++$i) {
            $unpacked = unpack('NsampleCount/NsampleOffset', $this->readHandle->read(8));
            if (!$unpacked) {
                throw new ParserException('Cannot parse entry.');
            }
            if ($this->version === 1) {
                $unpacked['sampleOffset'] = Integer::unsignedIntToSignedInt($unpacked['sampleOffset'], 32);
            }
            $this->entries[] = new SampleCountOffset($unpacked['sampleCount'], $unpacked['sampleOffset']);
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
