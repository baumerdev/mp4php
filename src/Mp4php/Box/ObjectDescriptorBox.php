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
 * Object Descriptor Box (type 'iods')
 *
 * aligned(8) class ObjectDescriptorBox
 *     extends FullBox(‘iods’, version = 0, 0) {
 *     ObjectDescriptor OD;
 * }
 */
class ObjectDescriptorBox extends AbstractFullBox
{
    const TYPE = 'iods';

    /**
     * {@inheritdoc}
     */
    protected function parse(): void
    {
        parent::parse();
        $this->seekToBoxEnd();
    }
}
