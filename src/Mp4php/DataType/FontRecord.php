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

/** *
 * class FontRecord {
 *   unsigned int(16) font-ID;
 *   unsigned int(8) font-name-length;
 *   unsigned int(8) font[font-name-length];
 * }
 *
 * Apple requires font name to be either "Serif" or "Sans-Serif"
 *
 * @see https://developer.apple.com/library/content/documentation/QuickTime/QTFF/QTFFChap3/qtff3.html#//apple_ref/doc/uid/TP40000939-CH205-SW80
 */
class FontRecord implements DataTypeInfoInterface
{
    /**
     * @var int
     */
    public $fontIdentifier;

    /**
     * @var string
     */
    public $fontName;

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}fontIdentifier: {$this->fontIdentifier}\n";
        echo "{$padding}fontName: {$this->fontName}\n";
    }
}
