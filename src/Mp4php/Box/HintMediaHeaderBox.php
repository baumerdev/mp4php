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
 * Hint Media Header Box (type 'hmhd')
 *
 * aligned(8) class HintMediaHeaderBox
 *     extends FullBox(‘hmhd’, version = 0, 0) {
 *     unsigned int(16) maxPDUsize;
 *     unsigned int(16) avgPDUsize;
 *     unsigned int(32) maxbitrate;
 *     unsigned int(32) avgbitrate;
 *     unsigned int(32) reserved = 0;
 * }
 *
 * @todo Parsing/writing
 */
class HintMediaHeaderBox extends AbstractMediaHeaderBox
{
    const TYPE = 'hmhd';
}
