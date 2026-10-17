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
 * class BoxRecord {
 *   signed int(16) top;
 *   signed int(16) left;
 *   signed int(16) bottom;
 *   signed int(16) right;
 * }
 */
readonly class BoxRecord implements DataTypeInfoInterface
{
    /**
     * BoxRecord constructor.
     */
    public function __construct(
        public int $top,
        public int $left,
        public int $bottom,
        public int $right,
    ) {
    }

    public function __toString(): string
    {
        return \sprintf('%d-%d-%d-%d', $this->top, $this->left, $this->bottom, $this->right);
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}top: {$this->top}\n";
        echo "{$padding}left: {$this->left}\n";
        echo "{$padding}bottom: {$this->bottom}\n";
        echo "{$padding}right: {$this->right}\n";
    }
}
