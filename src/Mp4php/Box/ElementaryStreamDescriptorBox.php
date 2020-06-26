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

/**
 * Elementary Stream Descriptor (type 'esds')
 *
 * aligned(8) class ESDBox
 * extends FullBox(‘esds’, version = 0, 0) {
 *   ES_Descriptor ES; (see ISO/IEC 14496-1, section 7.2.6.5)
 * }
 *
 * @todo Parsing
 */
class ElementaryStreamDescriptorBox extends AbstractFullBox
{
    const TYPE = 'esds';

    protected $container = [AbstractSampleEntryBox::class];

    /**
     * Read box's value
     */
    protected function parse(): void
    {
        parent::parse();

        $this->seekToBoxEnd();
    }
}
