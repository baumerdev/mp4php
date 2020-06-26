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

use Mp4php\DataType\PropertyQuantity;

/**
 * Movie Fragment Box (type 'moof')
 *
 * aligned(8) class MovieFragmentBox extends Box(‘moof’){
 * }
 */
class MovieFragmentBox extends Box
{
    const TYPE = 'moof';

    protected $boxImmutable = false;

    protected $container = [false];
    protected $classesProperties = [
        MovieFragmentHeaderBox::class => ['movieFragmentHeader', PropertyQuantity::ONE],
        TrackFragmentBox::class => ['trackFragments', PropertyQuantity::ZERO_OR_MORE],
    ];
    /**
     * @var MovieFragmentHeaderBox
     */
    protected $movieFragmentHeader;
    /**
     * @var TrackFragmentBox
     */
    protected $trackFragments;

    public function getMovieFragmentHeader(): MovieFragmentHeaderBox
    {
        return $this->movieFragmentHeader;
    }

    public function setMovieFragmentHeader(MovieFragmentHeaderBox $movieFragmentHeader): void
    {
        $this->movieFragmentHeader = $movieFragmentHeader;
        $this->setModified();
    }

    public function getTrackFragments(): TrackFragmentBox
    {
        return $this->trackFragments;
    }

    public function setTrackFragments(TrackFragmentBox $trackFragments): void
    {
        $this->trackFragments = $trackFragments;
        $this->setModified();
    }

    /**
     * Parse the box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
