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
 * Model for Sample Duration, Size, Flags & Offset
 */
readonly class SampleDurationSizeFlagsOffset
{
    /**
     * SampleDurationSizeFlagsOffset constructor.
     */
    public function __construct(
        public ?int $duration,
        public ?int $size,
        public ?int $flags,
        public ?int $offset,
    ) {
    }
}
