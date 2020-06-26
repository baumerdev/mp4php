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

use Mp4php\Box\Itunes\MetadataBox;
use Mp4php\DataType\PropertyQuantity;

/**
 * Meta Box (type 'meta')
 */
class MetaBox extends AbstractFullBox implements UserDataMetaInterface
{
    const TYPE = 'meta';

    protected $boxImmutable = false;

    protected $container = [false, UserDataBox::class];
    protected $classesProperties = [
        HandlerReferenceBox::class => ['handlerReference', PropertyQuantity::ONE],
        MetadataBox::class => ['metadata', PropertyQuantity::ZERO_OR_ONE],
    ];

    /**
     * @var HandlerReferenceBox
     */
    protected $handlerReference;
    /**
     * @var MetadataBox
     */
    protected $metadata;

    public function getHandlerReference(): HandlerReferenceBox
    {
        return $this->handlerReference;
    }

    public function setHandlerReference(HandlerReferenceBox $handlerReference): void
    {
        $this->handlerReference = $handlerReference;
        $this->setModified();
    }

    public function getMetadata(): MetadataBox
    {
        return $this->metadata;
    }

    public function setMetadata(MetadataBox $metadata): void
    {
        $this->metadata = $metadata;
        $this->setModified();
    }

    /**
     * Parse the Meta Box's Full Box data and it's children
     */
    protected function parse(): void
    {
        parent::parse();
        $this->parseChildren();
    }
}
