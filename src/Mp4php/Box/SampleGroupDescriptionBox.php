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
use Override;

/**
 * Sample Group Description (type 'sgpd')
 *
 * aligned(8) class SampleGroupDescriptionBox (unsigned int(32) handler_type)
 *   extends FullBox('sgpd', 0, 0){
 *   unsigned int(32) grouping_type;
 *   unsigned int(32) entry_count;
 *   int i;
 *   for (i = 1 ; i <= entry_count ; i++){
 *     switch (handler_type){
 *       case ‘vide’: // for video tracks
 *         VisualSampleGroupEntry ();
 *         break;
 *       case ‘soun’: // for audio tracks
 *         AudioSampleGroupEntry();
 *         break;
 *       case ‘hint’: // for hint tracks
 *         HintSampleGroupEntry();
 *         break;
 *     }
 *   }
 * }
 *
 * @todo Parse entries
 */
class SampleGroupDescriptionBox extends AbstractFullBox
{
    public const string TYPE = 'sgpd';

    protected array $container = [SampleTableBox::class];

    protected ?int $groupingType = null;
    protected ?int $entryCount = null;
    /**
     * @var array|null [int]
     */
    protected ?array $entries = null;
    /**
     * Offset the entries data start
     */
    protected ?int $entriesOffset = null;

    /**
     * Read box's value
     */
    #[Override]
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
