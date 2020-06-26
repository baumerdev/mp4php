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

use Exception;
use Mp4php\DataType\DateTime;
use Mp4php\DataType\Language;
use Mp4php\Exceptions\ParserException;

/**
 * Media Header Box (type 'mdhd')
 *
 * aligned(8) class MediaHeaderBox extends FullBox(‘mdhd’, version, 0) {
 *     if (version==1) {
 *         unsigned int(64) creation_time;
 *         unsigned int(64) modification_time;
 *         unsigned int(32) timescale;
 *         unsigned int(64) duration;
 *     } else { // version==0
 *         unsigned int(32) creation_time;
 *         unsigned int(32) modification_time;
 *         unsigned int(32) timescale;
 *         unsigned int(32) duration;
 *     }
 *     bit(1) pad = 0;
 *     unsigned int(5)[3] language; // ISO-639-2/T language code
 *     unsigned int(16) pre_defined = 0;
 * }
 */
class MediaHeaderBox extends AbstractFullBox
{
    const TYPE = 'mdhd';

    protected $boxImmutable = false;

    protected $container = [MediaBox::class];

    /**
     * @var DateTime|null
     */
    protected $creationTime;
    /**
     * @var DateTime|null
     */
    protected $modificationTime;
    /**
     * @var int
     */
    protected $timescale;
    /**
     * @var int
     */
    protected $duration;
    /**
     * @var string
     */
    protected $language;

    public function getCreationTime(): ?DateTime
    {
        return $this->creationTime;
    }

    public function setCreationTime(?DateTime $creationTime): void
    {
        if (($this->creationTime === null && $creationTime === null) ||
            ($this->creationTime !== null && $creationTime !== null &&
                $this->creationTime->getTimestamp() === $creationTime->getTimestamp())) {
            return;
        }

        $this->creationTime = $creationTime;
        $this->setModified();
    }

    public function getModificationTime(): ?DateTime
    {
        return $this->modificationTime;
    }

    public function setModificationTime(?DateTime $modificationTime): void
    {
        if (($this->modificationTime === null && $modificationTime === null) ||
            ($this->modificationTime !== null && $modificationTime !== null &&
                $this->modificationTime->getTimestamp() === $modificationTime->getTimestamp())) {
            return;
        }

        $this->modificationTime = $modificationTime;
        $this->setModified();
    }

    public function getTimescale(): int
    {
        return $this->timescale;
    }

    public function setTimescale(int $timescale): void
    {
        if ($this->timescale === $timescale) {
            return;
        }

        $this->timescale = $timescale;
        $this->setModified();
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        if ($this->duration === $duration) {
            return;
        }

        $this->duration = $duration;
        $this->setModified();
    }

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

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        if ($this->version === 1) {
            $unpackFormat = 'Jcreation/Jmodification/Ntimescale/Jduration/';
            $readLength = 32;
        } elseif ($this->version === 0) {
            $unpackFormat = 'Ncreation/Nmodification/Ntimescale/Nduration/';
            $readLength = 20;
        } else {
            throw new ParserException(sprintf('Version 0 or 1 expected but got %d', $this->version));
        }

        $unpackFormat .= 'H4language/npreDefined';

        $read = $this->readHandle->read($readLength);
        if ($unpacked = unpack($unpackFormat, $read)) {
            try {
                $this->creationTime = DateTime::createFromSecondsSince1904($unpacked['creation']);
            } catch (Exception $exception) {
                $this->creationTime = null;
            }
            try {
                $this->modificationTime = DateTime::createFromSecondsSince1904($unpacked['modification']);
            } catch (Exception $exception) {
                $this->modificationTime = null;
            }
            $this->timescale = $unpacked['timescale'];
            $this->duration = $unpacked['duration'];
            $this->language = Language::stringFromHex($unpacked['language']);
        } else {
            throw new ParserException('Cannot parse creationTime, modificationTime, timescale, duration');
        }
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        if ($this->version === 1) {
            $this->writeHandle->write(pack('J', $this->timestampOrZero($this->creationTime)));
            $this->writeHandle->write(pack('J', $this->timestampOrZero($this->modificationTime)));
            $this->writeHandle->write(pack('N', $this->timescale));
            $this->writeHandle->write(pack('J', $this->duration));
        } elseif ($this->version === 0) {
            $this->writeHandle->write(pack('N', $this->timestampOrZero($this->creationTime)));
            $this->writeHandle->write(pack('N', $this->timestampOrZero($this->modificationTime)));
            $this->writeHandle->write(pack('N', $this->timescale));
            $this->writeHandle->write(pack('N', $this->duration));
        } else {
            throw new ParserException(sprintf('Version 0 or 1 expected but got %d', $this->version));
        }

        // Language
        $this->writeHandle->write(Language::hexFromString($this->language));

        // Predefined
        $this->writeHandle->write(pack('n', 0));
    }

    /**
     * @return int
     */
    protected function timestampOrZero(?DateTime $dateTime)
    {
        if ($dateTime) {
            return $dateTime->getTimestamp1904();
        }

        return 0;
    }
}
