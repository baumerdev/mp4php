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
 * Media Box (type 'mdia')
 */
class MediaBox extends Box
{
    const TYPE = 'mdia';

    protected $boxImmutable = false;

    protected $container = [TrackBox::class];
    protected $classesProperties = [
        MediaHeaderBox::class => ['mediaHeader', PropertyQuantity::ONE],
        HandlerReferenceBox::class => ['handlerReference', PropertyQuantity::ONE],
        MediaInformationBox::class => ['mediaInformation', PropertyQuantity::ONE],
    ];
    /**
     * @var MediaHeaderBox|null
     */
    protected $mediaHeader;
    /**
     * @var HandlerReferenceBox|null
     */
    protected $handlerReference;
    /**
     * @var MediaInformationBox|null
     */
    protected $mediaInformation;

    public function getMediaHeader(): ?MediaHeaderBox
    {
        return $this->mediaHeader;
    }

    public function setMediaHeader(MediaHeaderBox $mediaHeader): void
    {
        $this->mediaHeader = $mediaHeader;
        $this->setModified();
    }

    public function getHandlerReference(): ?HandlerReferenceBox
    {
        return $this->handlerReference;
    }

    public function setHandlerReference(HandlerReferenceBox $handlerReference): void
    {
        $this->handlerReference = $handlerReference;
        $this->setModified();
    }

    public function getMediaInformation(): ?MediaInformationBox
    {
        return $this->mediaInformation;
    }

    public function setMediaInformation(MediaInformationBox $mediaInformation): void
    {
        $this->mediaInformation = $mediaInformation;
        $this->setModified();
    }

    /**
     * Parse the Media Box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
