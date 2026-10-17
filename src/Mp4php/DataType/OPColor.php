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

use Override;

/**
 * Model for graphics mode's OP color
 */
readonly class OPColor implements DataTypeInfoInterface
{
    /**
     * OPColor constructor.
     */
    public function __construct(
        public int $red,
        public int $green,
        public int $blue,
    ) {
    }

    #[Override]
    public function __toString(): string
    {
        return \sprintf('%d-%d-%d', $this->red, $this->green, $this->blue);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}rgb({$this->red}, {$this->green}, {$this->blue})\n";
    }
}
