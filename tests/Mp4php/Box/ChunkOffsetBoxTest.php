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

use Mp4php\Box\ChunkOffsetBox;

class ChunkOffsetBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new ChunkOffsetBox();

        $fileHandle->setHexContent('00000000ffffffff');
        $box->constructParse($fileHandle, 8);
        $this->assertEquals(2 ** 32 - 1, $box->getEntryCount());

        $fileHandle->setHexContent('000000000000000200000001ffffffff');
        $box->constructParse($fileHandle, 8);
        $this->assertEquals(2, $box->getEntryCount());

        $this->assertEquals([
            1, 2 ** 32 - 1,
        ], $box->entries());

        $this->assertEquals([
            1, 2 ** 32 - 1,
        ], $box->entries());
    }

    public function testSetEntries(): void
    {
        $box = new ChunkOffsetBox();

        $this->assertFalse($box->isModified());
        $box->setEntries([1, 2]);
        $this->assertTrue($box->isModified());

        $this->assertEquals(2, $box->getEntryCount());

        $this->assertEquals([
            1, 2,
        ], $box->entries());
    }

    public function testWrite(): void
    {
        $writeHandle = $this->memoryWriteHandle();

        $box = new ChunkOffsetBox();
        $box->setWriteHandle($writeHandle);
        $box->setEntries([1, 2 ** 32 - 1]);
        $box->write();

        $writeHandle->seek(0);
        $hexContent = bin2hex((string) $writeHandle->getContents());
        $this->assertEquals('000000177374636f000000000000000200000001ffffffff', $hexContent);
    }
}
