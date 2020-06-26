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

use Mp4php\Exceptions\BoxImmutableException;
use Mp4php\Exceptions\InvalidValueException;

/**
 * Full Box with additional version and flags
 *
 * aligned(8) class FullBox(unsigned int(32) boxtype, unsigned int(8) v, bit(24) f)
 *   extends Box(boxtype) {
 *   unsigned int(8) version = v;
 *   bit(24) flags = f;
 * }
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractFullBox extends Box
{
    /**
     * @var int
     */
    protected $version;
    /**
     * @var int
     */
    protected $flags;

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    public function updateVersionAndFlags(int $version, int $flags): void
    {
        if ($this->version === $version && $this->flags === $flags) {
            return;
        }

        $this->version = $version;
        $this->flags = $flags;

        $this->setModified();
    }

    /**
     * Copy data from read to write or write new data if mutable
     *
     * It simply copies the data if not modified otherwise it calls write methods on children
     * and writes size to box.
     *
     * In addition to parent::write() this writes version flags after type
     */
    public function write(?string $alternativeType = null): void
    {
        if (!$this->isModified()) {
            $this->writeHandle->copyData($this->readHandle, $this->offset, $this->size);

            return;
        }

        if ($this->boxImmutable) {
            throw new BoxImmutableException(sprintf('Box %s cannot be modified.', static::class));
        }

        $beginOffset = $this->writeTypeGetBoxOffset($alternativeType);
        $this->writeVersionFlags();

        $this->writeModifiedContent();

        $this->updateSizeAtOffset($beginOffset);
    }

    /**
     * Parse the Full Box's first four bytes for version and flags
     */
    protected function parse(): void
    {
        // Get 1 byte box version
        $versionBin = $this->readHandle->read(1);
        $this->version = unpack('C', $versionBin)[1];

        // Get 3 byte box flags
        $flags = $this->readHandle->read(3);

        $this->flags = (int) hexdec(bin2hex($flags));
    }

    /**
     * Check if flag is set (bitwise AND)
     */
    protected function checkFlag(int $flag): bool
    {
        return (bool) ($this->flags & $flag);
    }

    /**
     * Toggle flag (bitwise XOR)
     */
    protected function toggleFlag(int $flag): void
    {
        $this->flags = $this->flags ^ $flag;
    }

    /**
     * Activate flag (bitwise OR)
     */
    protected function activateFlag(int $flag): void
    {
        $this->flags = $this->flags | $flag;
    }

    /**
     * Deactivate flag (bitwise XOR NOT)
     */
    protected function deactivateFlag(int $flag): void
    {
        $this->flags = $this->flags & ~$flag;
    }

    /**
     * Write version and flags
     */
    protected function writeVersionFlags(): void
    {
        $this->writeHandle->write(pack('C', $this->version));
        $flagHex = sprintf('%06X', $this->flags);
        $flags = hex2bin($flagHex);

        if ($flags === false) {
            throw new InvalidValueException(sprintf('Flags "%s" invalid.', $flagHex));
        }

        $this->writeHandle->write($flags);
    }
}
