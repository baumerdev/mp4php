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

namespace tests\Mp4php\Box;

use Mp4php\Box\EditListBox;
use Mp4php\DataType\EditListEntry;

class EditListBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new EditListBox();

        $fileHandle->setHexContent('0000000000000000');
        $box->constructParse($fileHandle, 0);
        $this->assertEmpty($box->getEntries());

        $fileHandle->setHexContent('0000000000000001000000010000000200030004');
        $box->constructParse($fileHandle, 0);

        /** @var EditListEntry[] $entries */
        $entries = $box->getEntries();
        $this->assertCount(1, $entries);
        $entries = $box->getEntries();
        $this->assertCount(1, $entries);

        $this->assertInstanceOf(EditListEntry::class, $entries[0]);

        $this->assertEquals(1, $entries[0]->segmentDuration);
        $this->assertEquals(2, $entries[0]->mediaTime);
        $this->assertEquals(3, $entries[0]->mediaRate->integer);
        $this->assertEquals(4, $entries[0]->mediaRate->fraction);
    }

    public function testParseVersion1(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new EditListBox();

        $fileHandle->setHexContent('01000000000000010000000000000001000000000000000200030004');
        $box->constructParse($fileHandle, 0);

        /** @var EditListEntry[] $entries */
        $entries = $box->getEntries();
        $this->assertCount(1, $entries);
        $entries = $box->getEntries();
        $this->assertCount(1, $entries);

        $this->assertInstanceOf(EditListEntry::class, $entries[0]);

        $this->assertEquals(1, $entries[0]->segmentDuration);
        $this->assertEquals(2, $entries[0]->mediaTime);
        $this->assertEquals(3, $entries[0]->mediaRate->integer);
        $this->assertEquals(4, $entries[0]->mediaRate->fraction);
    }
}
