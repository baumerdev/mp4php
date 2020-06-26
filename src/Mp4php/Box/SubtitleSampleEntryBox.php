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

namespace Mp4php\Box;

use Mp4php\DataType\BoxRecord;
use Mp4php\DataType\PropertyQuantity;
use Mp4php\DataType\RGBAColor;
use Mp4php\DataType\StyleRecord;
use Mp4php\Exceptions\ParserException;

/**
 * Subtitle Sample Description (stsd entry for handlerType tx3g)
 *
 * class TextSampleEntry() extends SampleEntry (‘tx3g’) {
 *   unsigned int(32) displayFlags;
 *   signed int(8) horizontal-justification;
 *   signed int(8) vertical-justification;
 *   unsigned int(8) background-color-rgba[4];
 *   BoxRecord default-text-box;
 *   StyleRecord default-style;
 *   FontTableBox font-table;
 * }
 *
 * @see https://developer.apple.com/library/content/documentation/QuickTime/QTFF/QTFFChap3/qtff3.html#//apple_ref/doc/uid/TP40000939-CH205-SW80
 */
class SubtitleSampleEntryBox extends AbstractSampleEntryBox
{
    const TYPE = 'tx3g';

    const FLAG_SCROLL_IN = 0x00000020;
    const FLAG_SCROLL_OUT = 0x00000040;
    const FLAG_SCROLL_DIRECTION = 0x00000180;
    const FLAG_CONT_KARAOKE = 0x00000800;
    const FLAG_WRITE_TEXT_VERTICALLY = 0x00020000;
    const FLAG_FILL_TEXT_REGION = 0x00040000;
    const FLAG_SOME_SAMPLES_ARE_FORCED = 0x40000000;
    const FLAG_ALL_SAMPLES_ARE_FORCED = 0x80000000;
    const NO_SAMPLES_FORCED = 'no';
    const SOME_SAMPLES_FORCED = 'some';
    const ALL_SAMPLES_FORCED = 'all';

    protected $boxImmutable = false;

    protected $classesProperties = [
        FontTableBox::class => ['fontTable', PropertyQuantity::ONE],
    ];

    /**
     * @var int
     */
    protected $displayFlags;
    /**
     * @var string
     */
    protected $samplesAreForced;
    /**
     * @var int
     */
    protected $hJustification;
    /**
     * @var int
     */
    protected $vJustification;
    /**
     * @var RGBAColor
     */
    protected $backgroundColor;
    /**
     * @var BoxRecord
     */
    protected $boxRecord;
    /**
     * @var StyleRecord
     */
    protected $styleRecord;
    /**
     * @var FontTableBox
     */
    protected $fontTable;

    public function getDisplayFlags(): int
    {
        return $this->displayFlags;
    }

    public function samplesAreForced(): string
    {
        return $this->samplesAreForced;
    }

    public function setSamplesAreForced(string $samplesAreForced): void
    {
        if ($this->samplesAreForced === $samplesAreForced) {
            return;
        }

        $this->samplesAreForced = $samplesAreForced;
        $this->updateFlags();
        $this->setModified();
    }

    public function getHJustification(): int
    {
        return $this->hJustification;
    }

    public function setHJustification(int $hJustification): void
    {
        if ($this->hJustification === $hJustification) {
            return;
        }

        $this->hJustification = $hJustification;
        $this->setModified();
    }

    public function getVJustification(): int
    {
        return $this->vJustification;
    }

    public function setVJustification(int $vJustification): void
    {
        if ($this->vJustification === $vJustification) {
            return;
        }

        $this->vJustification = $vJustification;
        $this->setModified();
    }

    public function getBackgroundColor(): RGBAColor
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(RGBAColor $backgroundColor): void
    {
        if ((string) $this->backgroundColor === (string) $backgroundColor) {
            return;
        }

        $this->backgroundColor = $backgroundColor;
        $this->setModified();
    }

    public function getBoxRecord(): BoxRecord
    {
        return $this->boxRecord;
    }

    public function setBoxRecord(BoxRecord $boxRecord): void
    {
        if ((string) $this->boxRecord === (string) $boxRecord) {
            return;
        }

        $this->boxRecord = $boxRecord;
        $this->setModified();
    }

    public function getStyleRecord(): StyleRecord
    {
        return $this->styleRecord;
    }

    public function setStyleRecord(StyleRecord $styleRecord): void
    {
        if ((string) $this->styleRecord === (string) $styleRecord) {
            return;
        }

        $this->styleRecord = $styleRecord;
        $this->setModified();
    }

    public function getFontTable(): FontTableBox
    {
        return $this->fontTable;
    }

    public function setFontTable(FontTableBox $fontTable): void
    {
        $this->fontTable = $fontTable;
    }

    /**
     * Update flags with current settings
     */
    protected function updateFlags(): void
    {
        if ($this->samplesAreForced === self::ALL_SAMPLES_FORCED) {
            $this->displayFlags = $this->displayFlags | self::FLAG_ALL_SAMPLES_ARE_FORCED;
            $this->displayFlags = $this->displayFlags & ~self::FLAG_SOME_SAMPLES_ARE_FORCED;
        } elseif ($this->samplesAreForced === self::SOME_SAMPLES_FORCED) {
            $this->displayFlags = $this->displayFlags & ~self::FLAG_ALL_SAMPLES_ARE_FORCED;
            $this->displayFlags = $this->displayFlags | self::FLAG_SOME_SAMPLES_ARE_FORCED;
        } else {
            $this->displayFlags = $this->displayFlags & ~self::FLAG_ALL_SAMPLES_ARE_FORCED;
            $this->displayFlags = $this->displayFlags & ~self::FLAG_SOME_SAMPLES_ARE_FORCED;
        }
    }

    /**
     * Parse the Subtitle Sample Entry Box
     */
    protected function parse(): void
    {
        parent::parse();

        $this->displayFlags = (int) hexdec(bin2hex($this->readHandle->read(4)));

        if ($this->displayFlags & self::FLAG_ALL_SAMPLES_ARE_FORCED) {
            $this->samplesAreForced = self::ALL_SAMPLES_FORCED;
        } elseif ($this->displayFlags & self::FLAG_SOME_SAMPLES_ARE_FORCED) {
            $this->samplesAreForced = self::SOME_SAMPLES_FORCED;
        } else {
            $this->samplesAreForced = self::NO_SAMPLES_FORCED;
        }

        $unpacked = unpack(
            'chJust/cvJust/CbgrRed/CbgrGreen/CbgrBlue/CbgrAlpha/nboxTop/nboxLeft/nboxBottom/nboxRight/nstartChar/nendChar/nfontId/CfaceStyle/CfontSize/CtextRed/CtextGreen/CtextBlue/CtextAlpha',
            $this->readHandle->read(26)
        );
        if (!$unpacked) {
            throw new ParserException('Cannot parse Subtitle Sample Entry Box.');
        }

        $this->hJustification = $unpacked['hJust'];
        $this->vJustification = $unpacked['vJust'];

        $this->backgroundColor = new RGBAColor(
            $unpacked['bgrRed'],
            $unpacked['bgrGreen'],
            $unpacked['bgrBlue'],
            $unpacked['bgrAlpha']
        );
        $this->boxRecord = new BoxRecord(
            $unpacked['boxTop'],
            $unpacked['boxLeft'],
            $unpacked['boxBottom'],
            $unpacked['boxRight']
        );

        $styleRecord = new StyleRecord();
        $styleRecord->startChar = $unpacked['startChar'];
        $styleRecord->endChar = $unpacked['endChar'];
        $styleRecord->fontId = $unpacked['fontId'];
        $styleRecord->fontStyleFlags = $unpacked['faceStyle'];
        $styleRecord->fontSize = $unpacked['fontSize'];
        $styleRecord->textColor = new RGBAColor(
            $unpacked['textRed'],
            $unpacked['textGreen'],
            $unpacked['textBlue'],
            $unpacked['textAlpha']
        );
        $this->styleRecord = $styleRecord;

        $this->parseChildren();
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        $this->writeReservedReferenceIndex();

        $this->writeHandle->write((string) hex2bin(sprintf('%08x', $this->displayFlags)));

        $this->writeHandle->write(pack('c', $this->hJustification));
        $this->writeHandle->write(pack('c', $this->vJustification));

        $this->writeHandle->write(pack('C', $this->backgroundColor->red));
        $this->writeHandle->write(pack('C', $this->backgroundColor->green));
        $this->writeHandle->write(pack('C', $this->backgroundColor->blue));
        $this->writeHandle->write(pack('C', $this->backgroundColor->alpha));

        $this->writeHandle->write(pack('n', $this->boxRecord->top));
        $this->writeHandle->write(pack('n', $this->boxRecord->left));
        $this->writeHandle->write(pack('n', $this->boxRecord->bottom));
        $this->writeHandle->write(pack('n', $this->boxRecord->right));

        $this->writeHandle->write(pack('n', $this->styleRecord->startChar));
        $this->writeHandle->write(pack('n', $this->styleRecord->endChar));
        $this->writeHandle->write(pack('n', $this->styleRecord->fontId));
        $this->writeHandle->write(pack('C', $this->styleRecord->fontStyleFlags));
        $this->writeHandle->write(pack('C', $this->styleRecord->fontSize));

        $this->writeHandle->write(pack('C', $this->styleRecord->textColor->red));
        $this->writeHandle->write(pack('C', $this->styleRecord->textColor->green));
        $this->writeHandle->write(pack('C', $this->styleRecord->textColor->blue));
        $this->writeHandle->write(pack('C', $this->styleRecord->textColor->alpha));

        $this->writeChildren();
    }
}
