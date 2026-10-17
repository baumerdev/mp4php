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
use Override;

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
    public const string TYPE = 'sinf';

    protected array $container = [AbstractSampleEntryBox::class];

    protected array $classesProperties = [
        OriginalFormatBox::class => ['originalFormat', PropertyQuantity::ONE],
        SchemeTypeBox::class => ['schemeType', PropertyQuantity::ZERO_OR_ONE],
        SchemeInformationBox::class => ['schemeInfo', PropertyQuantity::ZERO_OR_ONE],
    ];

    protected ?OriginalFormatBox $originalFormat = null;
    protected ?SchemeTypeBox $schemeType = null;
    protected ?SchemeInformationBox $schemeInfo = null;

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

    #[Override]
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
