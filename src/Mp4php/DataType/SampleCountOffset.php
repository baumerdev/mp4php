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
 * Model for Sample Count, Sample Offset
 */
class SampleCountOffset
{
    /**
     * @var int
     */
    public $count;
    /**
     * @var int
     */
    public $offset;

    /**
     * SampleCountOffset constructor.
     */
    public function __construct(int $count, int $offset)
    {
        $this->count = $count;
        $this->offset = $offset;
    }
}
