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

namespace Mp4php\File;

/**
 * File handle wrapper for writing MP4 files to temp memory
 */
class MP4MemoryWriteHandle extends MP4WriteHandle
{
    /**
     * Create new MP4Handle with write access
     */
    public static function memoryWritingFile(): self
    {
        $handle = new self(null, self::TYPE_MEMORY);
        $handle->openReadWriteHandle();

        return $handle;
    }
}
