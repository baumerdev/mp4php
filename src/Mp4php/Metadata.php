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
    public ?string $title = null;
    public ?string $artist = null;
    public ?string $albumTitle = null;
    public ?string $albumArtist = null;
    public ?int $trackNumber = null;
    public ?int $trackNumberCount = null;
    public ?int $discNumber = null;
    public ?int $discNumberCount = null;
    public ?string $recordingDate = null;
    public ?bool $isPartOfCompilation = null;
}
