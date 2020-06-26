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
 * Data Reference Box (type 'dref')
 *
 * aligned(8) class DataReferenceBox
 *     extends FullBox(‘dref’, version = 0, 0) {
 *     unsigned int(32) entry_count;
 *     for (i=1; i <= entry_count; i++) {
 *         DataEntryBox(entry_version, entry_flags) data_entry;
 *     }
 * }
 */
class DataReferenceBox extends AbstractFullBox
{
    const TYPE = 'dref';

    protected $boxImmutable = false;

    protected $container = [DataInformationBox::class];

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

        $this->parseChildren();

        if (!\is_array($this->children) && $entryCount === 0) {
            return;
        }

        if (!\is_array($this->children) || \count($this->children) !== $entryCount) {
            throw new ParserException(sprintf('Parsed entries %d does not match entry count %d.', $this->children ? \count($this->children) : 0, $entryCount));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        if (\is_array($this->children)) {
            $this->writeHandle->write(pack('N', \count($this->children)));
            $this->writeChildren();
        } else {
            $this->writeHandle->write(pack('N', 0));
        }
    }
}
