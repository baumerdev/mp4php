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
 * Model for Sample Chunks
 */
class SampleChunk
{
    public ?int $firstChunk = null;
    public ?int $samplesPerChunk = null;
    public ?int $descriptionIndex = null;

    /**
     * SampleChunk constructor.
     */
    public function __construct(int $firstChunk, int $samplesPerChunk, int $descriptionIndex)
    {
        $this->firstChunk = $firstChunk;
        $this->samplesPerChunk = $samplesPerChunk;
        $this->descriptionIndex = $descriptionIndex;
    }
}
