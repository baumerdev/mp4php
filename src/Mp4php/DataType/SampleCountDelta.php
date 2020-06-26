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
 * Model for Sample Count, Sample Delta
 */
class SampleCountDelta
{
    /**
     * @var int
     */
    public $count;
    /**
     * @var int
     */
    public $delta;

    /**
     * SampleCountDelta constructor.
     */
    public function __construct(int $count, int $delta)
    {
        $this->count = $count;
        $this->delta = $delta;
    }
}
