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
 * Track Fragment Box (type 'traf')
 *
 * aligned(8) class TrackFragmentBox extends Box(‘traf’){
 * }
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackFragmentBox extends Box
{
    public const TYPE = 'traf';

    protected bool $boxImmutable = false;

    protected array $container = [MovieFragmentBox::class];
    protected array $classesProperties = [
        TrackFragmentHeaderBox::class => ['trackFragmentHeader', PropertyQuantity::ONE],
        TrackFragmentRunBox::class => ['trackFragmentRunBoxes', PropertyQuantity::ZERO_OR_MORE],
        SampleToGroupBox::class => ['sampleToGroup', PropertyQuantity::ZERO_OR_MORE],
    ];

    protected ?TrackFragmentHeaderBox $trackFragmentHeader = null;
    /**
     * @var TrackFragmentRunBox[]
     */
    protected ?array $trackFragmentRunBoxes = null;
    /**
     * @var SampleToGroupBox[]|null
     */
    protected ?array $sampleToGroup = null;

    public function getTrackFragmentHeader(): TrackFragmentHeaderBox
    {
        return $this->trackFragmentHeader;
    }

    public function setTrackFragmentHeader(TrackFragmentHeaderBox $trackFragmentHeader): void
    {
        $this->trackFragmentHeader = $trackFragmentHeader;
        $this->setModified();
    }

    /**
     * @return TrackFragmentRunBox[]
     */
    public function getTrackFragmentRunBoxes(): array
    {
        return $this->trackFragmentRunBoxes;
    }

    /**
     * @param TrackFragmentRunBox[] $trackFragmentRunBoxes
     */
    public function setTrackFragmentRunBoxes(array $trackFragmentRunBoxes): void
    {
        $this->trackFragmentRunBoxes = $trackFragmentRunBoxes;
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
     * Parse box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
