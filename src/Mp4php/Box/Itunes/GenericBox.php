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

namespace Mp4php\Box\Itunes;

use Mp4php\Exceptions\ParserException;

/**
 * Generic iTunes metadata box that usually contains plist data
 */
class GenericBox extends ValueBox
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $name;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        if ($this->url === $url) {
            return;
        }

        $this->url = $url;
        $this->setModified();
    }

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
     * Parse iTunes meta data
     *
     * @throws ParserException
     */
    protected function parse(): void
    {
        $mean = $this->readHandle->readDataForType('mean');
        if (substr($mean, 0, 4) !== "\x00\x00\x00\x00") {
            throw new ParserException('Required 4 null bytes not found.');
        }
        $this->url = substr($mean, 4);

        $name = $this->readHandle->readDataForType('name');
        if (substr($name, 0, 4) !== "\x00\x00\x00\x00") {
            throw new ParserException('Required 4 null bytes not found.');
        }
        $this->name = substr($name, 4);

        parent::parse();
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        $meanOffset = $this->writeTypeGetBoxOffset('mean');
        $this->writeHandle->write($this->url);
        $this->updateSizeAtOffset($meanOffset);

        $nameOffset = $this->writeTypeGetBoxOffset('name');
        $this->writeHandle->write($this->name);
        $this->updateSizeAtOffset($nameOffset);

        parent::writeModifiedContent();
    }
}
