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

use Mp4php\DataType\Language;
use Mp4php\Exceptions\ParserException;

/**
 * Title Box (type 'titl')
 */
class TitleBox extends AbstractFullBox implements UserDataMetaInterface
{
    const TYPE = 'titl';

    protected $boxImmutable = false;

    protected $container = [UserDataBox::class];

    /**
     * @var string
     */
    protected $language;
    /**
     * @var string
     */
    protected $title;

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        if ($this->language === $language) {
            return;
        }

        $this->language = $language;
        $this->setModified();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        if ($this->title === $title) {
            return;
        }

        $this->title = $title;
        $this->setModified();
    }

    /**
     * Read box's value
     */
    protected function parse(): void
    {
        parent::parse();

        if ($unpacked = unpack('H4language', $this->readHandle->read(2))) {
            $this->language = Language::stringFromHex($unpacked['language']);
        } else {
            throw new ParserException('Cannot parse language.');
        }

        $read = $this->readHandle->read($this->remainingBytes());
        if (\strlen($read) > 1) {
            $this->title = substr($read, 0, -1);
        }
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        // Language
        $this->writeHandle->write(Language::hexFromString($this->language));

        // Title
        $this->writeHandle->write(($this->title ?? '').\chr(0));
    }
}
