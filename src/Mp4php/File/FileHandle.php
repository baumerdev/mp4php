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

use Mp4php\Exceptions\FileReadException;
use Mp4php\Exceptions\FileWriteException;
use Mp4php\Exceptions\InvalidValueException;
use Mp4php\Exceptions\SizeException;
use RuntimeException;

/**
 * Wrapper for file handle (fopen)
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FileHandle
{
    const TYPE_FILE = 0;
    const TYPE_MEMORY = 1;

    /**
     * @var int self::TYPE_*
     */
    protected $type;
    /**
     * @var resource|null
     */
    protected $handle;
    /**
     * @var string
     */
    protected $filename;

    /**
     * Construct with class with filename
     *
     * @param string $filename
     * @param int    $type     self::TYPE_*
     */
    public function __construct(?string $filename = null, $type = 0)
    {
        if ($type !== self::TYPE_FILE && $type !== self::TYPE_MEMORY) {
            throw new InvalidValueException('Type must be either TYPE_FILE or TYPE_MEMORY.');
        }
        if ($this->type === self::TYPE_FILE && $filename === null) {
            throw new InvalidValueException('Filename must be given when constructing with TYPE_FILE.');
        }

        $this->filename = $filename;
        $this->type = $type;
    }

    /**
     * Open file for reading
     *
     * @throws FileReadException
     */
    public function openReadHandle(): void
    {
        if ($this->handle !== null) {
            throw new RuntimeException('Cannot open read handle because a handle is already open.');
        }

        if ($this->type === self::TYPE_MEMORY) {
            throw new FileReadException('Memory handles can only be opened for read/write.');
        }

        if (!file_exists($this->filename) || !is_readable($this->filename)) {
            throw new FileReadException("File {$this->filename} does not exist or is not readable.");
        }

        $handle = fopen($this->filename, 'r');
        if (!$handle) {
            throw new FileReadException("Failed opening {$this->filename} for reading.");
        }

        $this->handle = $handle;
    }

    /**
     * Open file for writing
     *
     * @throws FileWriteException
     */
    public function openWriteHandle(): void
    {
        if ($this->handle !== null) {
            throw new RuntimeException('Cannot open read handle because a handle is already open.');
        }

        if ($this->type === self::TYPE_MEMORY) {
            throw new FileWriteException('Memory handles can only be opened for read/write.');
        }

        if (file_exists($this->filename) || !is_writable(\dirname($this->filename))) {
            throw new FileWriteException("File {$this->filename} does exist or is not writable.");
        }

        $handle = fopen($this->filename, 'w');
        if (!$handle) {
            throw new FileWriteException("Failed opening {$this->filename} for writing.");
        }

        $this->handle = $handle;
    }

    /**
     * Open memory file for reading/writing
     *
     * @throws FileReadException
     */
    public function openReadWriteHandle(): void
    {
        if ($this->handle !== null) {
            throw new RuntimeException('Cannot open read/write handle because a handle is already open.');
        }

        if ($this->type !== self::TYPE_MEMORY) {
            throw new FileReadException('Only memory handles can be opened for read/write.');
        }

        $handle = fopen('php://memory', 'r+');
        if (!$handle) {
            throw new FileReadException('Failed opening memory for reading/writing.');
        }

        $this->handle = $handle;
    }

    /**
     * Return size of handle file
     *
     * @throws RuntimeException
     */
    public function size(): int
    {
        $fstat = fstat($this->handle());
        if ($fstat === false || !isset($fstat['size'])) {
            throw new RuntimeException('fstat did not return file size value.');
        }

        return $fstat['size'];
    }

    /**
     * Get the offset of the current handle
     */
    public function offset(): int
    {
        $offset = ftell($this->handle());
        if ($offset === false) {
            return 0;
        }

        return $offset;
    }

    /**
     * Seek handle to offset
     *
     * @return bool Success
     */
    public function seek(int $offset): bool
    {
        return fseek($this->handle(), $offset) === 0;
    }

    /**
     * Seek handle by offset from current position
     *
     * @return bool Success
     */
    public function seekBy(int $offset): bool
    {
        return fseek($this->handle(), $offset, SEEK_CUR) === 0;
    }

    /**
     * Rewind handle's pointer
     *
     * @return bool Success
     */
    public function rewind(): bool
    {
        return rewind($this->handle());
    }

    /**
     * Check if end of file is reached
     */
    public function isEndOfFile(): bool
    {
        if (feof($this->handle())) {
            return true;
        }

        // feof() only returns true if we tried to read after end of file, not if we are exactly at the end offset
        if ($this->size() === $this->offset()) {
            return true;
        }

        return false;
    }

    /**
     * Read number of bytes from handle
     *
     * @param bool $validateLength Validate if read length matches requested length
     */
    public function read(int $bytes, bool $validateLength = true): string
    {
//        if ($bytes < 1) {
//            throw new SizeException("Read length must be greater than 0.");
//        }

        $data = fread($this->handle(), $bytes);

        if ($validateLength === true) {
            if ($data === false) {
                throw new FileReadException(sprintf('Could not read %d bytes', $bytes));
            }
            if (\strlen($data) !== $bytes) {
                throw new SizeException(sprintf('Read size differs from expected size (expected %d, actual %d)', $bytes, \strlen($data)));
            }
        }

        if ($data === false) {
            throw new FileReadException(sprintf('Failed to read %d bytes.', $bytes));
        }

        return $data;
    }

    /**
     * Reads remainder of stream into a string
     *
     * @param int $maxLength The maximum bytes to read. Defaults to -1 (read all the remaining buffer).
     * @param int $offset    Seek to the specified offset before reading. Current position/no seeking if negative
     *
     * @return false|string
     */
    public function getContents($maxLength = -1, $offset = -1)
    {
        return stream_get_contents($this->handle(), $maxLength, $offset);
    }

    /**
     * Truncates a file to a given length
     *
     * @param int $size If size is larger than the file it is extended with NULL bytes
     *
     * @return bool Success
     */
    public function truncate(int $size): bool
    {
        return ftruncate($this->handle(), $size);
    }

    /**
     * Get the current opened handle
     *
     * @throws RuntimeException
     *
     * @return resource
     */
    protected function handle()
    {
        if (!$this->handle) {
            throw new RuntimeException('No handle is open.');
        }

        return $this->handle;
    }

    /**
     * Close open file handle
     */
    protected function closeHandle(): void
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }
}
