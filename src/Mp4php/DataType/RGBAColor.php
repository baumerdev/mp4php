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
 * RGBAColor
 */
class RGBAColor implements DataTypeInfoInterface
{
    public ?int $red = null;
    public ?int $green = null;
    public ?int $blue = null;
    public ?int $alpha = null;

    /**
     * OPColor constructor.
     */
    public function __construct(int $red, int $green, int $blue, int $alpha)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->alpha = $alpha;
    }

    public function __toString(): string
    {
        return \sprintf('%d-%d-%d-%d', $this->red, $this->green, $this->blue, $this->alpha);
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}rgba({$this->red}, {$this->green}, {$this->blue}, {$this->alpha})\n";
    }
}
