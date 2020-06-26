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

namespace Mp4php\DataType;

use RuntimeException;

/**
 * Helper for unsigned/signed int conversion since pack/unpack don't support signed Big Endian
 */
class Integer
{
    /**
     * Convert unsigned int to signed int
     *
     * @param int $int  Unsigned number
     * @param int $size Bits of number
     */
    public static function unsignedIntToSignedInt(int $int, int $size): int
    {
        if ($int < 0) {
            throw new RuntimeException("Unsigned integer $int is smaller than minimum.");
        }
        $maxUnsigned = 2 ** $size - 1;
        if ($int > $maxUnsigned) {
            throw new RuntimeException("Unsigned integer $int is larger than $size bit maximum.");
        }

        $max = 2 ** ($size - 1);
        if ($int >= $max) {
            return -1 * ($int - $max + 1);
        }

        return (int) $int;
    }

    /**
     * Convert signed int to unsigned int
     *
     * @param int $int  Signed number
     * @param int $size Bits of number
     */
    public static function signedIntToUnsignedInt(int $int, int $size): int
    {
        $min = 2 ** ($size - 1) * -1;
        $max = 2 ** ($size - 1) - 1;

        if ($int < $min || $int > $max) {
            throw new RuntimeException("Signed integer $int must be between $min and $max.");
        }

        if ($int < 0) {
            return -1 * $int + $max;
        }

        return (int) $int;
    }
}
