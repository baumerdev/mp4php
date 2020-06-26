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
 * Movie Extends Box (type 'mvex')
 *
 * aligned(8) class MovieExtendsBox extends Box(‘mvex’){
 * }
 */
class MovieExtendsBox extends Box
{
    const TYPE = 'mvex';

    protected $boxImmutable = false;

    protected $container = [MovieBox::class];
    protected $classesProperties = [
        MovieExtendsHeaderBox::class => ['movieExtendsHeader', PropertyQuantity::ZERO_OR_ONE],
    ];

    /**
     * @var MovieExtendsHeaderBox
     */
    protected $movieExtendsHeader;

    public function getMovieExtendsHeader(): MovieExtendsHeaderBox
    {
        return $this->movieExtendsHeader;
    }

    public function setMovieExtendsHeader(MovieExtendsHeaderBox $movieExtendsHeader): void
    {
        $this->movieExtendsHeader = $movieExtendsHeader;
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
