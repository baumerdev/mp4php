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

/**
 * aligned(8) class SchemeInformationBox extends Box('schi') {
 *   Box scheme_specific_data[];
 * }
 */
class SchemeInformationBox extends Box
{
    const TYPE = 'schi';

    protected $container = [ProtectionSchemeInfoBox::class];

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
