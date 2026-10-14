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
use Mp4php\Exceptions\ParserException;
use Mp4php\File\MP4ReadHandle;

/**
 * Media Information Box (type 'minf')
 */
class MediaInformationBox extends Box
{
    public const TYPE = 'minf';

    protected bool $boxImmutable = false;

    protected array $container = [MediaBox::class];
    protected array $classesProperties = [
        AbstractMediaHeaderBox::class => ['mediaHeader', PropertyQuantity::ONE],
        DataInformationBox::class => ['dataInformation', PropertyQuantity::ONE],
        SampleTableBox::class => ['sampleTable', PropertyQuantity::ONE],
    ];

    protected ?AbstractMediaHeaderBox $mediaHeader = null;
    protected ?DataInformationBox $dataInformation = null;
    protected ?SampleTableBox $sampleTable = null;
    protected ?string $handlerType = null;

    public function getMediaHeader(): AbstractMediaHeaderBox
    {
        return $this->mediaHeader;
    }

    public function setMediaHeader(AbstractMediaHeaderBox $mediaHeader): void
    {
        $this->mediaHeader = $mediaHeader;
        $this->setModified();
    }

    public function getDataInformation(): DataInformationBox
    {
        return $this->dataInformation;
    }

    public function setDataInformation(DataInformationBox $dataInformation): void
    {
        $this->dataInformation = $dataInformation;
        $this->setModified();
    }

    public function getSampleTable(): SampleTableBox
    {
        return $this->sampleTable;
    }

    public function setSampleTable(SampleTableBox $sampleTable): void
    {
        $this->sampleTable = $sampleTable;
        $this->setModified();
    }

    public function getHandlerType(): ?string
    {
        return $this->handlerType;
    }

    public function setHandlerType(string $handlerType): void
    {
        $this->handlerType = $handlerType;
        $this->setModified();
    }

    /**
     * {@inheritdoc}
     */
    public function constructParse(MP4ReadHandle $readHandle, ?int $size = null, ?int $largeSize = null, ?Box $parent = null)
    {
        /** @var MediaBox $parent */
        if (!\is_object($parent) || !is_a($parent, MediaBox::class) || $parent->getHandlerReference() === null) {
            throw new ParserException("'Media Box's Handler Reference Box needed for Media Information Box.");
        }

        $this->handlerType = $parent->getHandlerReference()->getHandlerType();

        return parent::constructParse($readHandle, $size, $largeSize, $parent);
    }

    /**
     * Parse the box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
