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
class SampleDurationSizeFlagsOffset
{
    /**
     * @var int|null
     */
    public $duration;
    /**
     * @var int|null
     */
    public $size;
    /**
     * @var int|null
     */
    public $flags;
    /**
     * @var int|null
     */
    public $offset;

    /**
     * SampleDurationSizeFlagsOffset constructor.
     */
    public function __construct(?int $duration, ?int $size, ?int $flags, ?int $offset)
    {
        $this->duration = $duration;
        $this->size = $size;
        $this->flags = $flags;
        $this->offset = $offset;
    }
}
