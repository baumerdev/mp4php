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
    public ?int $trackIndex = null;
    public ?int $trackID = null;
    public ?bool $enabled = null;
    public ?string $format = null;
    public ?string $codec = null;
    public ?string $title = null;
    public ?string $language = null;
    public ?int $alternateGroup = null;
    public ?float $duration = null;
}
