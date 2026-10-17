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
 * User Data Box (type 'udta')
 */
class UserDataBox extends Box
{
    public const string TYPE = 'udta';

    protected bool $boxImmutable = false;

    protected array $container = [MovieBox::class, TrackBox::class];
    protected array $classesProperties = [
        UserDataMetaInterface::class => ['meta', PropertyQuantity::ZERO_OR_MORE],
    ];

    /**
     * @var UserDataMetaInterface[]|null
     */
    protected ?array $meta = null;

    /**
     * @return UserDataMetaInterface[]|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * @param UserDataMetaInterface[]|null $meta
     */
    public function setMeta(?array $meta): void
    {
        $this->meta = $meta;
        $this->setModified();
    }

    /**
     * Parse the Data Box's children
     */
    #[Override]
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
