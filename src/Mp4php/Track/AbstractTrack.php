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

namespace Mp4php\Track;

/**
 * Simplified model for handling track data
 */
abstract class AbstractTrack
{
    /**
     * @var int|null
     */
    public $trackIndex;
    /**
     * @var int|null
     */
    public $trackID;
    /**
     * @var bool|null
     */
    public $enabled;
    /**
     * @var string|null
     */
    public $format;
    /**
     * @var string|null
     */
    public $codec;
    /**
     * @var string|null
     */
    public $title;
    /**
     * @var string|null
     */
    public $language;
    /**
     * @var int|null
     */
    public $alternateGroup;
    /**
     * @var float|null
     */
    public $duration;
}
