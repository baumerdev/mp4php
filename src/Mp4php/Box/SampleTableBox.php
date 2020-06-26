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
 * Sample Table Box (type 'stbl')
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleTableBox extends Box
{
    const TYPE = 'stbl';

    protected $boxImmutable = false;

    protected $container = [MediaInformationBox::class];
    protected $classesProperties = [
        SampleDescriptionBox::class => ['sampleDescription', PropertyQuantity::ONE],
        DecodingTimeToSampleBox::class => ['decodingTimeToSample', PropertyQuantity::ONE],
        SampleToChunkBox::class => ['sampleToChunk', PropertyQuantity::ONE],
        SampleSizeBox::class => ['sampleSize', PropertyQuantity::ONE],
        ChunkOffsetBox::class => ['chunkOffset', PropertyQuantity::ONE],
        CompositionTimeToSampleBox::class => ['compositionTimeToSample', PropertyQuantity::ZERO_OR_ONE],
        SyncSampleBox::class => ['syncSample', PropertyQuantity::ZERO_OR_ONE],
        SampleGroupDescriptionBox::class => ['sampleGroupDescription', PropertyQuantity::ZERO_OR_ONE],
        SampleToGroupBox::class => ['sampleToGroup', PropertyQuantity::ZERO_OR_MORE],
    ];
    /**
     * @var string
     */
    protected $handlerType;
    /**
     * @var SampleDescriptionBox
     */
    protected $sampleDescription;
    /**
     * @var DecodingTimeToSampleBox
     */
    protected $decodingTimeToSample;
    /**
     * @var SampleToChunkBox
     */
    protected $sampleToChunk;
    /**
     * @var SampleSizeBox
     */
    protected $sampleSize;
    /**
     * @var ChunkOffsetBox
     */
    protected $chunkOffset;
    /**
     * @var CompositionTimeToSampleBox|null
     */
    protected $compositionTimeToSample;
    /**
     * @var SyncSampleBox|null
     */
    protected $syncSample;
    /**
     * @var SampleGroupDescriptionBox|null
     */
    protected $sampleGroupDescription;
    /**
     * @var SampleToGroupBox[]|null
     */
    protected $sampleToGroup;

    /**
     * Getter for handlerType
     *
     * @return string
     */
    public function getHandlerType(): ?string
    {
        return $this->handlerType;
    }

    public function getSampleDescription(): SampleDescriptionBox
    {
        return $this->sampleDescription;
    }

    public function setSampleDescription(SampleDescriptionBox $sampleDescription): void
    {
        $this->sampleDescription = $sampleDescription;
        $this->setModified();
    }

    public function getDecodingTimeToSample(): DecodingTimeToSampleBox
    {
        return $this->decodingTimeToSample;
    }

    public function setDecodingTimeToSample(DecodingTimeToSampleBox $decodingTimeToSample): void
    {
        $this->decodingTimeToSample = $decodingTimeToSample;
        $this->setModified();
    }

    public function getSampleToChunk(): SampleToChunkBox
    {
        return $this->sampleToChunk;
    }

    public function setSampleToChunk(SampleToChunkBox $sampleToChunk): void
    {
        $this->sampleToChunk = $sampleToChunk;
        $this->setModified();
    }

    public function getSampleSize(): SampleSizeBox
    {
        return $this->sampleSize;
    }

    public function setSampleSize(SampleSizeBox $sampleSize): void
    {
        $this->sampleSize = $sampleSize;
        $this->setModified();
    }

    public function getChunkOffset(): ChunkOffsetBox
    {
        return $this->chunkOffset;
    }

    public function setChunkOffset(ChunkOffsetBox $chunkOffset): void
    {
        $this->chunkOffset = $chunkOffset;
        $this->setModified();
    }

    public function getCompositionTimeToSample(): ?CompositionTimeToSampleBox
    {
        return $this->compositionTimeToSample;
    }

    public function setCompositionTimeToSample(?CompositionTimeToSampleBox $compositionTimeToSample): void
    {
        $this->compositionTimeToSample = $compositionTimeToSample;
        $this->setModified();
    }

    public function getSyncSample(): ?SyncSampleBox
    {
        return $this->syncSample;
    }

    public function setSyncSample(?SyncSampleBox $syncSample): void
    {
        $this->syncSample = $syncSample;
        $this->setModified();
    }

    public function getSampleGroupDescription(): ?SampleGroupDescriptionBox
    {
        return $this->sampleGroupDescription;
    }

    public function setSampleGroupDescription(?SampleGroupDescriptionBox $sampleGroupDescription): void
    {
        $this->sampleGroupDescription = $sampleGroupDescription;
        $this->setModified();
    }

    /**
     * @return SampleToGroupBox[]|null
     */
    public function getSampleToGroup(): ?array
    {
        return $this->sampleToGroup;
    }

    /**
     * @param SampleToGroupBox[]|null $sampleToGroup
     */
    public function setSampleToGroup(?array $sampleToGroup): void
    {
        $this->sampleToGroup = $sampleToGroup;
        $this->setModified();
    }

    /**
     * {@inheritdoc}
     */
    public function constructParse(MP4ReadHandle $readHandle, ?int $size = null, ?int $largeSize = null, ?Box $parent = null)
    {
        /** @var $parent MediaInformationBox */
        if (!\is_object($parent) || !is_a($parent, MediaInformationBox::class) || $parent->getHandlerType() === null) {
            throw new ParserException("'Media Information Box's handlerType needed for Sample Table Box.");
        }

        $this->handlerType = $parent->getHandlerType();

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
