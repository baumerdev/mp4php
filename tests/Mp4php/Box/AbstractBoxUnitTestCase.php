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

use Mp4php\File\MP4MemoryWriteHandle;
use tests\AbstractUnitTestCase;
use tests\Mp4php\File\MockMP4ReadHandle;

abstract class AbstractBoxUnitTestCase extends AbstractUnitTestCase
{
    protected function mockMemoryReadHandle(): MockMP4ReadHandle
    {
        $fileHandle = new MockMP4ReadHandle(null, MockMP4ReadHandle::TYPE_MEMORY);
        $fileHandle->openReadWriteHandle();

        return $fileHandle;
    }

    protected function memoryWriteHandle(): MP4MemoryWriteHandle
    {
        return MP4MemoryWriteHandle::memoryWritingFile();
    }
}
