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
 * Media Data Box (type mdat)
 *
 * This box contains the actual media data like video, audio and subtitles.
 * This is only a big bunch of binary data in different formats and codecs that
 * can't be edited by this software. But we can move this box within the MP4 format
 * (to the end of the file) to provide a faststart option.
 */
class MediaDataBox extends Box
{
    const TYPE = 'mdat';

    protected $container = [false];
}
