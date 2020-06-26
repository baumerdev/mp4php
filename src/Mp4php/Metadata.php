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
 * Model for Metadata
 */
class Metadata
{
    /**
     * @var string|null
     */
    public $title;
    /**
     * @var string|null
     */
    public $artist;
    /**
     * @var string|null
     */
    public $albumTitle;
    /**
     * @var string|null
     */
    public $albumArtist;
    /**
     * @var int|null
     */
    public $trackNumber;
    /**
     * @var int|null
     */
    public $trackNumberCount;
    /**
     * @var int|null
     */
    public $discNumber;
    /**
     * @var int|null
     */
    public $discNumberCount;
    /**
     * @var string|null
     */
    public $recordingDate;
    /**
     * @var bool|null
     */
    public $isPartOfCompilation;
}
