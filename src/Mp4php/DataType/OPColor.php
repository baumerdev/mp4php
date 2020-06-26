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
 * Model for graphics mode's OP color
 */
class OPColor implements DataTypeInfoInterface
{
    /**
     * @var int
     */
    public $red;
    /**
     * @var int
     */
    public $green;
    /**
     * @var int
     */
    public $blue;

    /**
     * OPColor constructor.
     */
    public function __construct(int $red, int $green, int $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public function __toString(): string
    {
        return sprintf('%d-%d-%d', $this->red, $this->green, $this->blue);
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}rgb({$this->red}, {$this->green}, {$this->blue})\n";
    }
}
