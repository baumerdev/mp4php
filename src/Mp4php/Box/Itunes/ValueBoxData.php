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

namespace Mp4php\Box\Itunes;

use Mp4php\DataType\DataTypeInfoInterface;

/**
 * Data stored in ValueBox'es (0:n)
 */
class ValueBoxData implements DataTypeInfoInterface
{
    /**
     * @var int
     */
    public $type;
    /**
     * @var int
     */
    public $country;
    /**
     * @var int
     */
    public $language;
    /**
     * @var mixed
     */
    public $value;

    /**
     * ValueBoxData constructor.
     *
     * @param mixed $value
     */
    public function __construct(int $type, int $country, int $language, $value)
    {
        $this->type = $type;
        $this->country = $country;
        $this->language = $language;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}type: {$this->type}\n";
        echo "{$padding}country: {$this->country}\n";
        echo "{$padding}language: {$this->language}\n";
        echo "{$padding}value: {$this->value}\n";
    }
}
