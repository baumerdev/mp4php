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

use DateTime as PHPDateTime;
use Exception;

/**
 * DateTime extension with 1904 epoch (used by ISO)
 */
class DateTime extends PHPDateTime
{
    /**
     * Creates new DateTime object from seconds since 1904-01-01 midnight UTC
     *
     * @throws Exception
     */
    public static function createFromSecondsSince1904(int $seconds): ?self
    {
        if ($seconds < 1) {
            return null;
        }

        return new self('@'.(strtotime('1904-01-01 midnight UTC') + $seconds));
    }

    /**
     * Seconds since 1904-01-01 midnight UTC
     */
    public function getTimestamp1904(): int
    {
        return parent::getTimestamp() - strtotime('1904-01-01 midnight UTC');
    }
}
