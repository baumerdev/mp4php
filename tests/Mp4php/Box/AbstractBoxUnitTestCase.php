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

namespace Mp4php\Tests\Box;

use Mp4php\File\FileHandleType;
use Mp4php\File\MP4MemoryWriteHandle;
use Mp4php\Tests\AbstractUnitTestCase;
use Mp4php\Tests\File\MockMP4ReadHandle;

abstract class AbstractBoxUnitTestCase extends AbstractUnitTestCase
{
    protected function mockMemoryReadHandle(): MockMP4ReadHandle
    {
        $fileHandle = new MockMP4ReadHandle(null, FileHandleType::MEMORY);
        $fileHandle->openReadWriteHandle();

        return $fileHandle;
    }

    protected function memoryWriteHandle(): MP4MemoryWriteHandle
    {
        return MP4MemoryWriteHandle::memoryWritingFile();
    }
}
