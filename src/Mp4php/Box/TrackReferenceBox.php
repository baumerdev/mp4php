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
 * Track Reference Box (type 'tref')
 *
 * aligned(8) class TrackReferenceBox extends Box(‘tref’) {
 * }
 * aligned(8) class TrackReferenceTypeBox (unsigned int(32) reference_type) extends
 *     Box(reference_type) {
 *     unsigned int(32) track_IDs[];
 * }
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackReferenceBox extends Box
{
    const TYPE = 'tref';

    protected $boxImmutable = false;

    protected $container = [TrackBox::class];
    protected $classesProperties = [
        AbstractTrackReferenceTypeBox::class => [
            'references', PropertyQuantity::ZERO_OR_MORE,
        ],
    ];
    /**
     * @var AbstractTrackReferenceTypeBox[]|null
     */
    protected $references;

    /**
     * @return AbstractTrackReferenceTypeBox[]|null
     */
    public function getReferences(): ?array
    {
        return $this->references;
    }

    /**
     * @param AbstractTrackReferenceTypeBox[]|null $references
     */
    public function setReferences(?array $references): void
    {
        $this->references = $references;
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
