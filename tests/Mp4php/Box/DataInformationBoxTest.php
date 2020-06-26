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

use Mp4php\Box\DataInformationBox;
use Mp4php\Box\DataReferenceBox;
use TypeError;

class DataInformationBoxTest extends AbstractBoxUnitTestCase
{
    public function testBox(): void
    {
        $box = new DataInformationBox();

        $this->assertFalse($box->isModified());

        $dataRef = new DataReferenceBox();
        $box->setDataReference($dataRef);
        $this->assertTrue($box->isModified());
        $this->assertSame($box->getDataReference(), $dataRef);
    }

    public function testInvalidType(): void
    {
        $box = new DataInformationBox();

        $this->expectException(TypeError::class);
        $box->getDataReference();
    }
}
