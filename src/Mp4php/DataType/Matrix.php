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

use Mp4php\Exceptions\ConstructException;
use RuntimeException;

/**
 * Video transformation matrix
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Matrix implements DataTypeInfoInterface
{
    /**
     * @var FixedPoint
     */
    protected $a;
    /**
     * @var FixedPoint
     */
    protected $b;
    /**
     * @var FixedPoint
     */
    protected $u;
    /**
     * @var FixedPoint
     */
    protected $c;
    /**
     * @var FixedPoint
     */
    protected $d;
    /**
     * @var FixedPoint
     */
    protected $v;
    /**
     * @var FixedPoint
     */
    protected $x;
    /**
     * @var FixedPoint
     */
    protected $y;
    /**
     * @var FixedPoint
     */
    protected $w;

    /**
     * Matrix constructor.
     *
     * @param FixedPoint $a
     * @param FixedPoint $b
     * @param FixedPoint $u
     * @param FixedPoint $c
     * @param FixedPoint $d
     * @param FixedPoint $v
     * @param FixedPoint $x
     * @param FixedPoint $y
     * @param FixedPoint $w
     */
    public function __construct(?FixedPoint $a = null, ?FixedPoint $b = null, ?FixedPoint $u = null, ?FixedPoint $c = null, ?FixedPoint $d = null, ?FixedPoint $v = null, ?FixedPoint $x = null, ?FixedPoint $y = null, ?FixedPoint $w = null)
    {
        $this->a = $a;
        $this->b = $b;
        $this->u = $u;
        $this->c = $c;
        $this->d = $d;
        $this->v = $v;
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s-%s-%s-%s-%s-%s-%s-%s-%s',
            $this->a,
            $this->b,
            $this->u,
            $this->c,
            $this->d,
            $this->v,
            $this->x,
            $this->y,
            $this->w
        );
    }

    /**
     * New matrix with hex string
     */
    public static function createWithHexString(string $hex): self
    {
        $bin = hex2bin($hex);
        if ($bin === false) {
            throw new ConstructException(sprintf('"%s" is not a valid hex value.', $hex));
        }

        return self::createWithBytes($bin);
    }

    /**
     * New matrix with bytes
     */
    public static function createWithBytes(string $bin): self
    {
        $unpacked = unpack('Na/Nb/Nu/Nc/Nd/Nv/Nx/Ny/Nw', $bin);
        if ($unpacked) {
            return new self(
                FixedPoint::createFromInt($unpacked['a'], 16, 16),
                FixedPoint::createFromInt($unpacked['b'], 16, 16),
                FixedPoint::createFromInt($unpacked['u'], 2, 30),
                FixedPoint::createFromInt($unpacked['c'], 16, 16),
                FixedPoint::createFromInt($unpacked['d'], 16, 16),
                FixedPoint::createFromInt($unpacked['v'], 2, 30),
                FixedPoint::createFromInt($unpacked['x'], 16, 16),
                FixedPoint::createFromInt($unpacked['y'], 16, 16),
                FixedPoint::createFromInt($unpacked['w'], 2, 30)
            );
        }

        throw new RuntimeException('Cannot parse data to matrix values.');
    }

    /**
     * Convert matrix to binary string
     */
    public function toBytes(): string
    {
        return pack(
            'NNNNNNNNN',
            $this->a->toInt(),
            $this->b->toInt(),
            $this->u->toInt(),
            $this->c->toInt(),
            $this->d->toInt(),
            $this->v->toInt(),
            $this->x->toInt(),
            $this->y->toInt(),
            $this->w->toInt()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}a: ";
        $this->a->info(0);
        echo "{$padding}b: ";
        $this->b->info(0);
        echo "{$padding}u: ";
        $this->u->info(0);
        echo "{$padding}c: ";
        $this->c->info(0);
        echo "{$padding}d: ";
        $this->d->info(0);
        echo "{$padding}v: ";
        $this->v->info(0);
        echo "{$padding}x: ";
        $this->x->info(0);
        echo "{$padding}y: ";
        $this->y->info(0);
        echo "{$padding}w: ";
        $this->w->info(0);
    }
}
