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
 * Edit Box (type 'edts')
 */
class EditBox extends Box
{
    public const TYPE = 'edts';

    protected bool $boxImmutable = false;

    protected array $container = [TrackBox::class];
    protected array $classesProperties = [
        EditListBox::class => ['editList', PropertyQuantity::ZERO_OR_ONE],
    ];

    protected ?EditListBox $editList = null;

    public function getEditList(): EditListBox
    {
        return $this->editList;
    }

    public function setEditList(EditListBox $editList): void
    {
        $this->editList = $editList;
        $this->setModified();
    }

    /**
     * Parse the Edit Box's children
     */
    protected function parse(): void
    {
        $this->parseChildren();
    }
}
