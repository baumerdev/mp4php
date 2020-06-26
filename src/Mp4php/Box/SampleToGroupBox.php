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
 * Sample Group Description (type 'sbgp')
 *
 * aligned(8) class SampleToGroupBox
 *   extends FullBox(‘sbgp’, version = 0, 0)
 * {
 *   unsigned int(32) grouping_type;
 *   unsigned int(32) entry_count;
 *   for (i=1; i <= entry_count; i++)
 *   {
 *     unsigned int(32) sample_count;
 *     unsigned int(32) group_description_index;
 *   }
 * }
 *
 * @todo Parse entries
 */
class SampleToGroupBox extends AbstractFullBox
{
    const TYPE = 'sbgp';

    protected $container = [SampleTableBox::class, TrackFragmentBox::class];

    /**
     * @var int
     */
    protected $groupingType;
    /**
     * @var int
     */
    protected $entryCount;
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

    /**
     * Read box's value
     */
    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack('H8groupingType/NentryCount', $this->readHandle->read(8));
        if (!$unpacked) {
            throw new ParserException('Cannot parse grouping type/entry count.');
        }

        $this->groupingType = (int) hex2bin($unpacked['groupingType']);
        $this->entryCount = $unpacked['entryCount'];

        $this->entriesOffset = $this->readHandle->offset();

        $this->seekToBoxEnd();
    }
}
