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

use Mp4php\Box\ChunkLargeOffsetBox;

class ChunkLargeOffsetBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new ChunkLargeOffsetBox();

        $fileHandle->setHexContent('000000000000000200000000000000017fffffffffffffff');
        $box->constructParse($fileHandle, 8);
        $this->assertEquals(2, $box->getEntryCount());

        $this->assertEquals([
            1, 2 ** 63 - 1,
        ], $box->entries());
    }

    public function testSetEntries(): void
    {
        $box = new ChunkLargeOffsetBox();

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

        $box = new ChunkLargeOffsetBox();
        $box->setWriteHandle($writeHandle);
        $box->setEntries([1, 2 ** 32 - 1]);
        $box->write();

        $writeHandle->seek(0);
        $hexContent = bin2hex((string) $writeHandle->getContents());
        $this->assertEquals('0000001f636f36340000000000000002000000000000000100000000ffffffff', $hexContent);
    }
}
