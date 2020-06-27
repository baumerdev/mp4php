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

use Mp4php\Box\VisualSampleEntryBox;
use Mp4php\DataType\FixedPoint;

class VisualSampleEntryBoxTest extends AbstractBoxUnitTestCase
{
    const TEST_HEAD = '0000005558585858';
    const TEST_DATA = '000000000000ffff00000000000000000000000000000000078004380048000000480000000000000019636f6d70726573736f722020202020202020202020202020202020202020202000180000';

    public function testParse(): void
    {
        $fileHandle = $this->mockMemoryReadHandle();

        $box = new VisualSampleEntryBox(null, 'XXXX');

        $fileHandle->setContent((string) hex2bin(self::TEST_DATA));
        $box->constructParse($fileHandle, 86);

        $this->assertEquals(1920, $box->getWidth());
        $this->assertEquals(1080, $box->getHeight());
        $this->assertEquals(25, $box->getFrameCount());
        $this->assertEquals('compressor', trim($box->getCompressorName()));
        $this->assertEquals(24, $box->getDepth());

        $hRes = $box->getHorizontalResolution();
        $this->assertEquals(72, $hRes->integer);
        $this->assertEquals(0, $hRes->fraction);
        $vRes = $box->getVerticalResolution();
        $this->assertEquals(72, $vRes->integer);
        $this->assertEquals(0, $vRes->fraction);

        $box->setWidth(1920);
        $box->setHeight(1080);
        $box->setFrameCount(25);
        $box->setCompressorName(str_pad('compressor', 32));
        $box->setDepth(24);
        $hRes = FixedPoint::createFromInt(4718592, 16, 16);
        $box->setHorizontalResolution($hRes);
        $vRes = FixedPoint::createFromInt(4718592, 16, 16);
        $box->setVerticalResolution($vRes);

        $this->assertFalse($box->isModified());
    }

    public function testWrite(): void
    {
        $writeHandle = $this->memoryWriteHandle();
        $box = new VisualSampleEntryBox(null, 'XXXX');
        $box->setWriteHandle($writeHandle);

        $box->setWidth(1920);
        $box->setHeight(1080);
        $box->setFrameCount(25);
        $box->setCompressorName('compressor');
        $box->setDepth(24);

        $hRes = FixedPoint::createFromInt(4718592, 16, 16);
        $box->setHorizontalResolution($hRes);
        $vRes = FixedPoint::createFromInt(4718592, 16, 16);
        $box->setVerticalResolution($vRes);

        $box->setDataReferenceIndex(65535);

        $this->assertTrue($box->isModified());

        $box->write();

        $writeHandle->seek(0);
        $hexContent = bin2hex((string) $writeHandle->getContents());
        $this->assertEquals(self::TEST_HEAD.self::TEST_DATA, $hexContent);
    }

    public function testPixelAspectRatio(): void
    {
        $box = new VisualSampleEntryBox(null, 'XXXX');

        $this->assertNull($box->getPixelAspectRatio());
        $this->assertFalse($box->isModified());

        $box->setPixelAspectRatio(null);
        $this->assertTrue($box->isModified());
    }

    public function testEsDescriptor(): void
    {
        $box = new VisualSampleEntryBox(null, 'XXXX');

        $this->assertNull($box->getEsDescriptor());
        $this->assertFalse($box->isModified());

        $box->setEsDescriptor(null);
        $this->assertTrue($box->isModified());
    }
}
