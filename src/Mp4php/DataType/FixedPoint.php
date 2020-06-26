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

use RuntimeException;

/**
 * Class for handling fixed point floating values and conversion from/to integer
 */
class FixedPoint implements DataTypeInfoInterface
{
    /**
     * @var int
     */
    public $integer;
    /**
     * @var int
     */
    public $fraction;
    /**
     * @var int
     */
    protected $sizeInteger;
    /**
     * @var int
     */
    protected $sizeFraction;

    /**
     * FixedPoint constructor with sizes for integer and fraction
     */
    public function __construct(int $sizeInteger, int $sizeFraction)
    {
        $this->sizeInteger = $sizeInteger;
        $this->sizeFraction = $sizeFraction;
    }

    /**
     * Return value as string;
     */
    public function __toString(): string
    {
        if ($this->integer === 0 && $this->fraction === 0) {
            return '0';
        }
        if ($this->fraction === 0) {
            return "{$this->integer}";
        }

        return "{$this->integer}.{$this->fraction}";
    }

    /**
     * New instance with int converted to integer and fraction
     */
    public static function createFromInt(int $int, int $sizeInteger, int $sizeFraction): self
    {
        $fixedPoint = new self($sizeInteger, $sizeFraction);
        $fixedPoint->setInt($int);

        return $fixedPoint;
    }

    /**
     * Convert int to integer and fraction using instances sizes
     *
     * @param int $int Bit size must equal of sizeInteger + sizeFraction
     */
    public function setInt($int): void
    {
        $maxNumber = 2 ** ($this->sizeInteger + $this->sizeFraction) - 1;
        if ($int > $maxNumber) {
            throw new RuntimeException("Integer cannot be larger than maximum for {$this->sizeInteger}.{$this->sizeFraction} number $maxNumber.");
        }

        $divisor = 2 ** $this->sizeFraction;

        $this->integer = (int) ($int / $divisor);
        $this->fraction = $int % $divisor;
    }

    /**
     * Convert integer and fraction to combined value using instance sizes
     */
    public function toInt(): int
    {
        $divisor = 2 ** $this->sizeFraction;

        return $this->integer * $divisor + $this->fraction;
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}".$this->__toString()."\n";
    }
}
