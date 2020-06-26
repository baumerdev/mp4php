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
 * Simplified model for handling audi track data
 */
class AudioTrack extends AbstractTrack
{
    /**
     * @var string|null
     */
    public $volume;
    /**
     * @var int|null
     */
    public $subtitle;
    /**
     * @var string|null
     */
    public $sampleRate;
    /**
     * @var int|null
     */
    public $bitrate;
    /**
     * @var string|null
     */
    public $channels;
}
