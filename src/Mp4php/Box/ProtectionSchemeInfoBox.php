<?php
/*
 * MP4PHP
 * PHP library for parsing and modifying MP4 files
 *
 * Copyright © 2016-2021 Markus Baumer <markus@baumer.dev>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 * the GNU General Public License for more details.
 */

declare(strict_types=1);

namespace Mp4php\Box;

use Mp4php\DataType\PropertyQuantity;

/**
 * Protection Scheme Information Box (sinf)
 *
 * aligned(8) class ProtectionSchemeInfoBox(fmt) extends Box('sinf') {
 *   OriginalFormatBox(fmt) original_format;
 *   SchemeTypeBox scheme_type_box; // optional
 *   SchemeInformationBox info; // optional
 * }
 */
class ProtectionSchemeInfoBox extends Box
{
    const TYPE = 'sinf';

    protected $container = [AbstractSampleEntryBox::class];

    protected $classesProperties = [
        OriginalFormatBox::class => ['originalFormat', PropertyQuantity::ONE],
        SchemeTypeBox::class => ['schemeType', PropertyQuantity::ZERO_OR_ONE],
        SchemeInformationBox::class => ['schemeInfo', PropertyQuantity::ZERO_OR_ONE],
    ];

    /**
     * @var OriginalFormatBox
     */
    protected $originalFormat;
    /**
     * @var SchemeTypeBox|null
     */
    protected $schemeType;
    /**
     * @var SchemeInformationBox|null
     */
    protected $schemeInfo;

    public function getOriginalFormat(): OriginalFormatBox
    {
        return $this->originalFormat;
    }

    public function getSchemeType(): ?SchemeTypeBox
    {
        return $this->schemeType;
    }

    public function getSchemeInfo(): ?SchemeInformationBox
    {
        return $this->schemeInfo;
    }

    protected function parse(): void
    {
        $this->parseChildren();
    }
}
