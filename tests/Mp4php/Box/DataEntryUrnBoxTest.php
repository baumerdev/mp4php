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

use Mp4php\Box\DataEntryUrnBox;
use Mp4php\Exceptions\SizeException;

class DataEntryUrnBoxTest extends AbstractBoxUnitTestCase
{
    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new DataEntryUrnBox();

        $fileHandle->setHexContent('00000001');
        $box->constructParse($fileHandle, 12);
        $this->assertNull($box->getUrn());
        $this->assertNull($box->getLocation());

        $fileHandle->setHexContent('00000000544553540074657374');
        $box->constructParse($fileHandle, 21);
        $this->assertEquals('TEST', $box->getUrn());
        $this->assertEquals('test', $box->getLocation());

        $fileHandle->setHexContent('00000000');
        $this->expectException(SizeException::class);
        $box->constructParse($fileHandle, 12);
    }
}
