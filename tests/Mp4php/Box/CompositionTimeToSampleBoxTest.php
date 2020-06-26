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

use Mp4php\Box\CompositionTimeToSampleBox;
use Mp4php\DataType\SampleCountOffset;

class CompositionTimeToSampleBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new CompositionTimeToSampleBox();

        $fileHandle->setHexContent('00000000ffffffff');
        $box->constructParse($fileHandle, 8);
        $this->assertEquals(2 ** 32 - 1, $box->getEntryCount());

        $fileHandle->setHexContent('00000000000000020000000100000002ffffffffffffffff');
        $box->constructParse($fileHandle, 16);
        $this->assertEquals(2, $box->getEntryCount());

        /** @var SampleCountOffset[] $entries */
        $entries = $box->entries();
        $this->assertCount(2, $entries);
        $entries = $box->entries();
        $this->assertCount(2, $entries);

        $this->assertInstanceOf(SampleCountOffset::class, $entries[0]);
        $this->assertInstanceOf(SampleCountOffset::class, $entries[1]);

        $this->assertEquals(1, $entries[0]->count);
        $this->assertEquals(2, $entries[0]->offset);
        $this->assertEquals(2 ** 32 - 1, $entries[1]->count);
        $this->assertEquals(2 ** 32 - 1, $entries[1]->offset);
    }

    public function testParseVersion1(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new CompositionTimeToSampleBox();
        $fileHandle->setHexContent('01000000000000020000000100000002ffffffffffffffff');
        $box->constructParse($fileHandle, 16);
        $this->assertEquals(2, $box->getEntryCount());

        /** @var SampleCountOffset[] $entries */
        $entries = $box->entries();
        $this->assertCount(2, $entries);
        $entries = $box->entries();
        $this->assertCount(2, $entries);

        $this->assertInstanceOf(SampleCountOffset::class, $entries[0]);
        $this->assertInstanceOf(SampleCountOffset::class, $entries[1]);

        $this->assertEquals(1, $entries[0]->count);
        $this->assertEquals(2, $entries[0]->offset);
        $this->assertEquals(2 ** 32 - 1, $entries[1]->count);
        $this->assertEquals(2 ** 31 * -1, $entries[1]->offset);
    }
}
