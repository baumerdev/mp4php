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

/**
 * Name Box (type 'name')
 *
 * @see https://developer.apple.com/library/content/documentation/QuickTime/QTFF/Metadata/Metadata.html
 */
class NameBox extends Box implements UserDataMetaInterface
{
    const TYPE = 'name';

    protected $boxImmutable = false;

    protected $container = [UserDataBox::class];

    /**
     * @var string
     */
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($this->name === $name) {
            return;
        }

        $this->name = $name;
        $this->setModified();
    }

    /**
     * Read box's value
     */
    protected function parse(): void
    {
        $this->name = $this->readHandle->read($this->remainingBytes());
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        // Name
        $this->writeHandle->write($this->name ?? '');
    }
}
