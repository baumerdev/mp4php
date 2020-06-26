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
 * File handle wrapper for writing MP4 files
 */
class MP4WriteHandle extends AbstractMP4Handle
{
    /**
     * Create new MP4Handle with write access
     */
    public static function writingFile(string $filename): self
    {
        $handle = new self($filename);
        $handle->openWriteHandle();

        return $handle;
    }

    /**
     * Write data to current write handle
     */
    public function write(string $data): void
    {
        fwrite($this->handle(), $data);
    }

    /**
     * Copy data from read handle with offset and size and write to write handle
     */
    public function copyData(MP4ReadHandle $readHandle, int $offset, int $size): void
    {
        $megabyte = 1024 * 1024;

        $readHandle->seek($offset);
        while ($size > 0) {
            $readSize = $size > $megabyte ? $megabyte : $size;
            $size -= $readSize;

            $data = $readHandle->read($readSize);
            $this->write($data);
        }
    }
}
