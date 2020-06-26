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
 * Track Box (type 'trak')
 */
class TrackBox extends Box
{
    const TYPE = 'trak';

    protected $boxImmutable = false;

    protected $container = [MovieBox::class];
    protected $classesProperties = [
        TrackHeaderBox::class => ['trackHeader', PropertyQuantity::ONE],
        EditBox::class => ['editBox', PropertyQuantity::ZERO_OR_ONE],
        MediaBox::class => ['mediaBox', PropertyQuantity::ONE],
        TrackReferenceBox::class => ['trackReference', PropertyQuantity::ZERO_OR_ONE],
        UserDataBox::class => ['userData', PropertyQuantity::ZERO_OR_ONE],
    ];

    /**
     * @var TrackHeaderBox
     */
    protected $trackHeader;
    /**
     * @var EditBox
     */
    protected $editBox;
    /**
     * @var MediaBox
     */
    protected $mediaBox;
    /**
     * @var TrackReferenceBox|null
     */
    protected $trackReference;
    /**
     * @var UserDataBox|null
     */
    protected $userData;

    public function getTrackHeader(): TrackHeaderBox
    {
        return $this->trackHeader;
    }

    public function setTrackHeader(TrackHeaderBox $trackHeader): void
    {
        $this->trackHeader = $trackHeader;
        $this->setModified();
    }

    public function getEditBox(): EditBox
    {
        return $this->editBox;
    }

    public function setEditBox(EditBox $editBox): void
    {
        $this->editBox = $editBox;
        $this->setModified();
    }

    public function getMediaBox(): MediaBox
    {
        return $this->mediaBox;
    }

    public function setMediaBox(MediaBox $mediaBox): void
    {
        $this->mediaBox = $mediaBox;
        $this->setModified();
    }

    public function getTrackReference(): ?TrackReferenceBox
    {
        return $this->trackReference;
    }

    public function setTrackReference(?TrackReferenceBox $trackReference): void
    {
        $this->trackReference = $trackReference;
        $this->setModified();
    }

    public function getUserData(): ?UserDataBox
    {
        return $this->userData;
    }

    public function setUserData(?UserDataBox $userData): void
    {
        $this->userData = $userData;
        $this->setModified();
    }

    /**
     * Parse the Track Box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
