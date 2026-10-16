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

namespace Mp4php;

/**
 * How Save writes a modified Movie Box back to the file
 */
enum SaveScenario
{
    case OVERWRITE;
    case OVERWRITE_TRUNCATE_FILE;
    case OVERWRITE_RESIZE_FREE;
    case REPLACE_FREE_MOVE_TO_END;
}
