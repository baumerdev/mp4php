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
 * Model for entries in Edit List Box
 */
class EditListEntry implements DataTypeInfoInterface
{
    /**
     * @var int
     */
    public $segmentDuration;
    /**
     * @var int
     */
    public $mediaTime;
    /**
     * @var FixedPoint 16.16
     */
    public $mediaRate;

    /**
     * EditListEntry constructor.
     */
    public function __construct(int $segmentDuration, int $mediaTime, int $mediaRateInteger, int $mediaRateFraction)
    {
        $this->segmentDuration = $segmentDuration;
        $this->mediaTime = $mediaTime;

        $this->mediaRate = new FixedPoint(16, 16);
        $this->mediaRate->integer = $mediaRateInteger;
        $this->mediaRate->fraction = $mediaRateFraction;
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}segmentDuration: {$this->segmentDuration}\n";
        echo "{$padding}mediaTime: {$this->mediaTime}\n";
        echo "{$padding}mediaRate: ";
        $this->mediaRate->info(0);
    }
}
