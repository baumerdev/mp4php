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
 * Movie Box (type 'moov')
 */
class MovieBox extends Box
{
    const TYPE = 'moov';

    protected $boxImmutable = false;

    protected $container = [false];
    protected $classesProperties = [
        MovieHeaderBox::class => ['movieHeader', PropertyQuantity::ONE],
        ObjectDescriptorBox::class => ['objectDescriptor', PropertyQuantity::ZERO_OR_ONE],
        TrackBox::class => ['tracks', PropertyQuantity::ONE_OR_MORE],
        UserDataBox::class => ['userData', PropertyQuantity::ZERO_OR_ONE],
        MovieExtendsBox::class => ['movieExtends', PropertyQuantity::ZERO_OR_ONE],
    ];
    /**
     * @var MovieHeaderBox
     */
    protected $movieHeader;
    /**
     * @var ObjectDescriptorBox
     */
    protected $objectDescriptor;
    /**
     * @var TrackBox[]
     */
    protected $tracks;
    /**
     * @var UserDataBox|null
     */
    protected $userData;
    /**
     * @var MovieExtendsBox
     */
    protected $movieExtends;

    public function getMovieHeader(): MovieHeaderBox
    {
        return $this->movieHeader;
    }

    public function setMovieHeader(MovieHeaderBox $movieHeader): void
    {
        $this->movieHeader = $movieHeader;
        $this->setModified();
    }

    public function getObjectDescriptor(): ObjectDescriptorBox
    {
        return $this->objectDescriptor;
    }

    public function setObjectDescriptor(ObjectDescriptorBox $objectDescriptor): void
    {
        $this->objectDescriptor = $objectDescriptor;
        $this->setModified();
    }

    /**
     * @return TrackBox[]
     */
    public function getTracks(): array
    {
        return $this->tracks;
    }

    /**
     * @param TrackBox[] $tracks
     */
    public function setTracks(array $tracks): void
    {
        $this->tracks = $tracks;
        $this->setModified();
    }

    public function getUserData(): ?UserDataBox
    {
        return $this->userData;
    }

    public function setUserData(UserDataBox $userData): void
    {
        $this->userData = $userData;
        $this->setModified();
    }

    public function getMovieExtends(): MovieExtendsBox
    {
        return $this->movieExtends;
    }

    public function setMovieExtends(MovieExtendsBox $movieExtends): void
    {
        $this->movieExtends = $movieExtends;
        $this->setModified();
    }

    /**
     * Parse the Movie Box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
