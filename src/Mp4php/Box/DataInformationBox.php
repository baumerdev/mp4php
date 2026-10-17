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
use Override;

/**
 * Data Information Box (type 'dinf')
 */
class DataInformationBox extends Box
{
    public const string TYPE = 'dinf';

    protected bool $boxImmutable = false;

    protected array $container = [MediaInformationBox::class, MetaBox::class];
    protected array $classesProperties = [
        DataReferenceBox::class => ['dataReference', PropertyQuantity::ONE],
    ];

    protected ?DataReferenceBox $dataReference = null;

    public function getDataReference(): DataReferenceBox
    {
        return $this->dataReference;
    }

    public function setDataReference(DataReferenceBox $dataReference): void
    {
        $this->dataReference = $dataReference;
        $this->setModified();
    }

    /**
     * Parse the box's children
     */
    #[Override]
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
