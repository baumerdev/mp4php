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

use Mp4php\DataType\EditListEntry;
use Mp4php\Exceptions\ParserException;

/**
 * Edit List Box (type 'elst')
 */
class EditListBox extends AbstractFullBox
{
    const TYPE = 'elst';

    protected $container = [EditBox::class];

    /**
     * @var EditListEntry[]
     */
    protected $entries = [];

    /**
     * @return EditListEntry[]
     */
    public function getEntries(): array
    {
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
        $entryCount = $unpacked['entryCount'];

        $entries = [];
        for ($i = 1; $i <= $entryCount; ++$i) {
            if ($this->version === 1) {
                $unpackFormat = 'Jduration/JmediaTime/nmediaRateInteger/nmediaRateFraction';
                $readLength = 20;
            } elseif ($this->version === 0) {
                $unpackFormat = 'Nduration/NmediaTime/nmediaRateInteger/nmediaRateFraction';
                $readLength = 12;
            } else {
                throw new ParserException(sprintf('Version 0 or 1 expected but got "%d"', $this->version));
            }

            $read = $this->readHandle->read($readLength);
            if ($unpacked = unpack($unpackFormat, $read)) {
                $entries[] = new EditListEntry(
                    $unpacked['duration'],
                    $unpacked['mediaTime'],
                    $unpacked['mediaRateInteger'],
                    $unpacked['mediaRateFraction']
                );
            }
        }
        $this->entries = $entries;
    }
}
