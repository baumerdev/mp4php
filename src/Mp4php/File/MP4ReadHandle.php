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

use Mp4php\Exceptions\SizeException;
use Mp4php\Exceptions\TypeException;

/**
 * File handle wrapper for reading MP4 files
 */
class MP4ReadHandle extends AbstractMP4Handle
{
    /**
     * Create new MP4Handle with read access
     */
    public static function readingFile(string $filename): self
    {
        $handle = new self($filename);
        $handle->openReadHandle();

        return $handle;
    }

    /**
     * Read data with given type from handle at current pointer
     */
    public function readDataForType(string $type): string
    {
        $sizeType = $this->readSizeType($type);
        $size = $sizeType[1];
        $largeSize = $sizeType[2];

        if ($largeSize !== null) {
            $size = $largeSize - 16;
        } else {
            $size -= 8;
        }

        return $this->readDataWithSize($size);
    }

    /**
     * Read data with given size from handle at current pointer
     *
     * @param int $size Bytes to read
     *
     * @throws TypeException
     */
    public function readDataWithSize(int $size): string
    {
        $data = fread($this->handle(), $size);
        if ($data === false || \strlen($data) < $size) {
            throw new TypeException(sprintf('Unable to read %d bytes of data.', $size));
        }

        return $data;
    }

    /**
     * Read handle at current pointer for size, type and largeSize (if size == 1)
     *
     * @throws SizeException
     *
     * @return array [string $type, int|null $size, int|null $largeSize]
     */
    public function readSizeType(?string $expectedType = null): array
    {
        $size = $this->readSize();
        $type = $this->readType($expectedType);

        $largeSize = null;
        if ($size === 1) {
            $size = null;
            $largeSize = $this->readLargeSize();
        }

        return [$type, $size, $largeSize];
    }

    /**
     * Read 32-bit box size from handle at current pointer
     *
     * @throws SizeException
     */
    protected function readSize(): int
    {
        $sizeBin = fread($this->handle(), 4);
        if ($sizeBin === false || \strlen($sizeBin) < 4) {
            throw new SizeException('Unable to get 32-bit size value.');
        }
        $size = unpack('N', $sizeBin)[1];
        // Size can be 0 (to end of file) and 1 (64bit value)
        if ($size > 1 && $size < 8) {
            throw new SizeException('Size is too small (must be at least 8 including size and type).');
        }

        return $size;
    }

    /**
     * Read 64-bit size from handle at current pointer
     *
     * @throws SizeException
     */
    protected function readLargeSize(): int
    {
        $largeSizeBin = fread($this->handle(), 8);
        if ($largeSizeBin === false || \strlen($largeSizeBin) < 8) {
            throw new SizeException('Unable to get 64-bit large size value.');
        }
        $largeSize = unpack('J', $largeSizeBin)[1];
        if ($largeSize < 16) {
            throw new SizeException('Size is too small (must be at least 16 including size, type, largeSize).');
        }

        return $largeSize;
    }

    /**
     * Read 4-char size from handle at current pointer
     *
     * @param string|null $expectedType If set, read type must match this value
     *
     * @throws TypeException
     */
    protected function readType(?string $expectedType = null): string
    {
        $type = fread($this->handle(), 4);

        if ($type === false || \strlen($type) < 4) {
            throw new TypeException('Unable to get 32bit type value.');
        }

        if ($expectedType !== null && $expectedType !== $type) {
            throw new TypeException(sprintf('Type "%s" did not match expected type "%s".', $type, $expectedType));
        }

        return $type;
    }
}
