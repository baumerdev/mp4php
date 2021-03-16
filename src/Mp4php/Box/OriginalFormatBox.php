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
 * aligned(8) class OriginalFormatBox(codingname) extends Box ('frma') {
 *   unsigned int(32) data_format = codingname;
 *   // format of decrypted, encoded data (in case of protection)
 *   // or un-transformed sample entry (in case of restriction
 *   // and complete track information)
 * }
 */
class OriginalFormatBox extends Box
{
    const TYPE = 'frma';

    protected $container = [ProtectionSchemeInfoBox::class];

    /**
     * @var string
     */
    protected $originalFormat;

    public function getOriginalFormat(): string
    {
        return $this->originalFormat;
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        $this->originalFormat = $this->readHandle->read(4);
        $this->seekToBoxEnd();
    }
}
