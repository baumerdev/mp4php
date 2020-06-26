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

namespace Mp4php\Box;

use Mp4php\Exceptions\SizeException;

/**
 * Free Space Box (types 'free' or 'skip')
 */
class FreeSpaceBox extends Box
{
    const TYPE = 'free';
    const TYPE_SKIP = 'skip';
    const TYPE_WIDE = 'wide';

    protected $boxImmutable = false;

    /**
     * This box can have a manually set size that will be used for writing empty bytes
     *
     * Any box shorter 8 bytes is invalid because type and size fields are each 4 bytes. And
     * an empty box is impossible since it would result in a size of "0" which is reserved for
     * 'box ends at end of file'.
     *
     * @param int $size The box size including 8 bit for type and size
     */
    public function setSize(int $size): void
    {
        if ($size < 9) {
            throw new SizeException(sprintf('File size %d cannot be smaller than 9.', $size));
        }

        $this->size = $size;

        $this->setModified();
    }

    /**
     * This box is always "modified" because any content of it's original block
     * is irrelevant so we can simply write zero-values.
     */
    public function isModified(): bool
    {
        return true;
    }

    /**
     * Create a default free box with given size
     *
     * @param int $size
     *
     * @return FreeSpaceBox
     */
    public function constructDefault($size)
    {
        $this->setSize($size);
        $this->headerSize = 8;

        return $this;
    }

    /**
     * Just write null bytes with given length
     */
    protected function writeModifiedContent(): void
    {
        $size = $this->size - 8;
        while ($size > 0) {
            $readSize = $size > 1024 ? 1024 : $size;
            $size -= $readSize;

            $data = str_repeat("\0", $readSize);
            $this->writeHandle->write($data);
        }
    }
}
