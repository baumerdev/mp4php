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
 * aligned(8) class StyleRecord {
 *   unsigned int(16) startChar;
 *   unsigned int(16) endChar;
 *   unsigned int(16) font-ID;
 *   unsigned int(8) face-style-flags;
 *   unsigned int(8) font-size;
 *   unsigned int(8) text-color-rgba[4];
 * }
 */
class StyleRecord implements DataTypeInfoInterface
{
    /**
     * @var int|null
     */
    public $startChar;
    /**
     * @var int|null
     */
    public $endChar;
    /**
     * @var int|null
     */
    public $fontId;
    /**
     * @var int|null
     */
    public $fontStyleFlags;
    /**
     * @var int|null
     */
    public $fontSize;
    /**
     * @var RGBAColor|null
     */
    public $textColor;

    public function __toString(): string
    {
        return sprintf(
            '%d-%d-%d-%d-%d-%s',
            $this->startChar,
            $this->endChar,
            $this->fontId,
            $this->fontStyleFlags,
            $this->fontSize,
            $this->textColor ?? ''
        );
    }

    /**
     * {@inheritdoc}
     */
    public function info(int $level): void
    {
        $padding = str_repeat('    ', $level);
        echo "{$padding}startChar: {$this->startChar}\n";
        echo "{$padding}endChar: {$this->endChar}\n";
        echo "{$padding}fontId: {$this->fontId}\n";
        echo "{$padding}fontStyleFlags: {$this->fontStyleFlags}\n";
        echo "{$padding}fontSize: {$this->fontSize}\n";
        echo "{$padding}textColor: ";
        if ($this->textColor) {
            $this->textColor->info(0);
        } else {
            echo "\n";
        }
    }
}
