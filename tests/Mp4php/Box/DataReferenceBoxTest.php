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

use Mp4php\Box\DataEntryUrlBox;
use Mp4php\Box\DataReferenceBox;
use Mp4php\Exceptions\ParserException;

class DataReferenceBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new DataReferenceBox();

        $fileHandle->setHexContent('0000000000000000');
        $box->constructParse($fileHandle, 16);
        $this->assertNull($box->getChildren());

        $fileHandle->setHexContent('0000000000000001');
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Parsed entries 0 does not match entry count 1.');
        $box->constructParse($fileHandle, 16);

        $fileHandle->setHexContent('00000000000000010000000c75726c2000000001');
        $box->constructParse($fileHandle, 28);
        $children = $box->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(DataEntryUrlBox::class, $children[0]);
    }

    public function testWrite(): void
    {
        $writeHandle = $this->memoryWriteHandle();

        $box = new DataReferenceBox();
        $box->setOffset(12);
        $box->setModified(true);
        $box->setWriteHandle($writeHandle);
        $box->write();

        $writeHandle->seek(0);
        $hexContent = bin2hex((string) $writeHandle->getContents());
        $this->assertEquals('0000000f647265660000000000000000', $hexContent);

        $readHandle = $this->mockMemoryReadHandle();
        $readHandle->setHexContent('00000000000000010000000c75726c2000000001');
        $writeHandle = $this->memoryWriteHandle();

        $box = new DataReferenceBox();
        $box->constructParse($readHandle, 28);
        $box->setModified(true);
        $box->setWriteHandle($writeHandle);
        $box->write();
        $writeHandle->seek(0);
        $hexContent = bin2hex((string) $writeHandle->getContents());
        $this->assertEquals('0000001b6472656600000000000000010000000c75726c2000000001', $hexContent);
    }
}
