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

namespace Mp4php\Box\Itunes;

use Mp4php\Box\MetaBox;

/**
 * Metadata Box (type 'ilst')
 */
class MetadataBox extends AbstractItunesBox
{
    const TYPE = 'ilst';

    protected $boxImmutable = false;

    protected $container = [MetaBox::class];

    /**
     * Parse the Metadata Box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
