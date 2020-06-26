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
 * Unknown Media Header Box as first child of Media Information Box if unknown type
 */
class UnknownMediaHeaderBox extends AbstractMediaHeaderBox
{
    /**
     * Parse Full Box Header and seek to box's end
     */
    protected function parse(): void
    {
        parent::parse();
        $this->seekToBoxEnd();
    }
}
