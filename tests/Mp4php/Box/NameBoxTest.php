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

use Mp4php\Box\NameBox;

class NameBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new NameBox();

        $fileHandle->setContent('Test1111');
        $box->constructParse($fileHandle, 0);

        $this->assertEquals('Test1111', $box->getName());

        $box->setName('Test1111');
        $this->assertEquals('Test1111', $box->getName());
        $this->assertFalse($box->isModified());

        $box->setName('Test2222');
        $this->assertEquals('Test2222', $box->getName());
        $this->assertTrue($box->isModified());
    }

    public function testWrite(): void
    {
        $writeHandle = $this->memoryWriteHandle();

        $box = new NameBox();
        $box->setWriteHandle($writeHandle);
        $box->setName('');

        $box->write();
        $writeHandle->seek(0);
        $this->assertEquals('000000076e616d65', bin2hex((string) $writeHandle->getContents()));

        $writeHandle = $this->memoryWriteHandle();

        $box = new NameBox();
        $box->setWriteHandle($writeHandle);
        $box->setName('Test3333');

        $box->write();
        $writeHandle->seek(0);
        $this->assertEquals('0000000f6e616d655465737433333333', bin2hex((string) $writeHandle->getContents()));
    }
}
