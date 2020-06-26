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

/**
 * Helper for language conversion
 */
class Language
{
    /**
     * Convert 1-bit padding and 3x 3-bit hex to 3-char language string
     */
    public static function stringFromHex(string $hex): string
    {
        $binary = str_pad(decbin((int) hexdec($hex)), 16, '0', STR_PAD_LEFT);

        $language = \chr(bindec(substr($binary, 1, 5)) + 96).
            \chr(bindec(substr($binary, 6, 5)) + 96).
            \chr(bindec(substr($binary, 11, 5)) + 96);

        if (preg_match('@^[a-z]{3}$@', $language)) {
            return $language;
        }

        return 'und';
    }

    /**
     * Convert 3-letter language string to binary 2 byte representation
     */
    public static function hexFromString(string $string): string
    {
        if (!preg_match('@[a-z]{3}@', $string)) {
            $string = 'und';
        }

        $ord1 = decbin(\ord(substr($string, 0, 1)) - 96);
        $ord2 = decbin(\ord(substr($string, 1, 1)) - 96);
        $ord3 = decbin(\ord(substr($string, 2, 1)) - 96);

        $binString = '0'.
            str_pad("$ord1", 5, '0', STR_PAD_LEFT).
            str_pad("$ord2", 5, '0', STR_PAD_LEFT).
            str_pad("$ord3", 5, '0', STR_PAD_LEFT);

        return \chr(bindec(substr($binString, 0, 8))).\chr(bindec(substr($binString, 8, 8)));
    }
}
