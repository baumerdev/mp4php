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
class BoxRecord implements DataTypeInfoInterface
{
    /**
     * @var int
     */
    public $top;
    /**
     * @var int
     */
    public $left;
    /**
     * @var int
     */
    public $bottom;
    /**
     * @var int
     */
    public $right;

    /**
     * BoxRecord constructor.
     */
    public function __construct(int $top, int $left, int $bottom, int $right)
    {
        $this->top = $top;
        $this->left = $left;
        $this->bottom = $bottom;
        $this->right = $right;
    }

    public function __toString(): string
    {
        return sprintf('%d-%d-%d-%d', $this->top, $this->left, $this->bottom, $this->right);
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
