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
use Mp4php\DataType\FixedPoint;
use Mp4php\DataType\Integer;
use Mp4php\DataType\Matrix;
use Mp4php\Exceptions\InvalidValueException;
use Mp4php\Exceptions\ParserException;

/**
 * Track Header Box (type 'tkhd')
 *
 * aligned(8) class TrackHeaderBox
 *     extends FullBox(‘tkhd’, version, flags){
 *     if (version==1) {
 *         unsigned int(64) creation_time;
 *         unsigned int(64) modification_time;
 *         unsigned int(32) track_ID;
 *         const unsigned int(32) reserved = 0;
 *         unsigned int(64) duration;
 *     } else { // version==0
 *         unsigned int(32) creation_time;
 *         unsigned int(32) modification_time;
 *         unsigned int(32) track_ID;
 *         const unsigned int(32) reserved = 0;
 *         unsigned int(32) duration;
 *     }
 *     const unsigned int(32)[2] reserved = 0;
 *     template int(16) layer = 0;
 *     template int(16) alternate_group = 0;
 *     template int(16) volume = {if track_is_audio 0x0100 else 0};
 *     const unsigned int(16) reserved = 0;
 *     template int(32)[9] matrix=
 *         { 0x00010000,0,0,0,0x00010000,0,0,0,0x40000000 };
 *         // unity matrix
 *     unsigned int(32) width;
 *     unsigned int(32) height;
 * }
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TrackHeaderBox extends AbstractFullBox
{
    const TYPE = 'tkhd';

    const FLAG_TRACK_ENABLED = 1;
    const FLAG_TRACK_IN_MOVIE = 2;
    const FLAG_TRACK_IN_PREVIEW = 4;
    const FLAG_TRACK_IN_POSTER = 8;

    protected $boxImmutable = false;

    protected $container = [TrackBox::class];

    /**
     * @var bool
     */
    protected $trackEnabled;
    /**
     * @var bool
     */
    protected $trackInMovie;
    /**
     * @var bool
     */
    protected $trackInPreview;
    /**
     * @var bool
     */
    protected $trackInPoster;
    /**
     * @var DateTime|null
     */
    protected $creationTime;
    /**
     * @var DateTime|null
     */
    protected $modificationTime;
    /**
     * @var int|null
     */
    protected $trackId;
    /**
     * @var int
     */
    protected $duration;
    /**
     * @var int
     */
    protected $layer;
    /**
     * @var int
     */
    protected $alternateGroup;
    /**
     * @var FixedPoint 8.8
     */
    protected $volume;
    /**
     * @var Matrix
     */
    protected $matrix;
    /**
     * @var FixedPoint 16.16
     */
    protected $width;
    /**
     * @var FixedPoint 16.16
     */
    protected $height;

    public function isTrackEnabled(): bool
    {
        return $this->trackEnabled;
    }

    public function setTrackEnabled(bool $trackEnabled): void
    {
        if ($this->trackEnabled === $trackEnabled) {
            return;
        }

        $this->trackEnabled = $trackEnabled;
        $this->toggleFlag(static::FLAG_TRACK_ENABLED);
        $this->setModified();
    }

    public function isTrackInMovie(): bool
    {
        return $this->trackInMovie;
    }

    public function setTrackInMovie(bool $trackInMovie): void
    {
        if ($this->trackInMovie === $trackInMovie) {
            return;
        }

        $this->trackInMovie = $trackInMovie;
        $this->toggleFlag(static::FLAG_TRACK_IN_MOVIE);
        $this->setModified();
    }

    public function isTrackInPreview(): bool
    {
        return $this->trackInPreview;
    }

    public function setTrackInPreview(bool $trackInPreview): void
    {
        if ($this->trackInPreview === $trackInPreview) {
            return;
        }

        $this->trackInPreview = $trackInPreview;
        $this->toggleFlag(static::FLAG_TRACK_IN_PREVIEW);
        $this->setModified();
    }

    public function isTrackInPoster(): bool
    {
        return $this->trackInPoster;
    }

    public function setTrackInPoster(bool $trackInPoster): void
    {
        if ($this->trackInPoster === $trackInPoster) {
            return;
        }

        $this->trackInPoster = $trackInPoster;
        $this->toggleFlag(static::FLAG_TRACK_IN_POSTER);
        $this->setModified();
    }

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

    public function getTrackId(): ?int
    {
        return $this->trackId;
    }

    public function setTrackId(?int $trackId): void
    {
        if ($this->trackId === $trackId) {
            return;
        }

        $this->trackId = $trackId;
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

    public function getLayer(): int
    {
        return $this->layer;
    }

    public function setLayer(int $layer): void
    {
        if ($this->layer === $layer) {
            return;
        }

        $this->layer = $layer;
        $this->setModified();
    }

    public function getAlternateGroup(): int
    {
        return $this->alternateGroup;
    }

    public function setAlternateGroup(int $alternateGroup): void
    {
        if ($this->alternateGroup === $alternateGroup) {
            return;
        }

        $this->alternateGroup = $alternateGroup;
        $this->setModified();
    }

    public function getVolume(): FixedPoint
    {
        return $this->volume;
    }

    public function setVolume(FixedPoint $volume): void
    {
        if ((string) $this->volume === (string) $volume) {
            return;
        }

        $this->volume = $volume;
        $this->setModified();
    }

    public function getMatrix(): Matrix
    {
        return $this->matrix;
    }

    public function setMatrix(Matrix $matrix): void
    {
        if ((string) $this->matrix === (string) $matrix) {
            return;
        }

        $this->matrix = $matrix;
        $this->setModified();
    }

    public function getWidth(): FixedPoint
    {
        return $this->width;
    }

    public function setWidth(FixedPoint $width): void
    {
        if ((string) $this->width === (string) $width) {
            return;
        }

        $this->width = $width;
        $this->setModified();
    }

    public function getHeight(): FixedPoint
    {
        return $this->height;
    }

    public function setHeight(FixedPoint $height): void
    {
        if ((string) $this->height === (string) $height) {
            return;
        }

        $this->height = $height;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $this->trackEnabled = $this->checkFlag(self::FLAG_TRACK_ENABLED);
        $this->trackInMovie = $this->checkFlag(self::FLAG_TRACK_IN_MOVIE);
        $this->trackInPreview = $this->checkFlag(self::FLAG_TRACK_IN_PREVIEW);
        $this->trackInPoster = $this->checkFlag(self::FLAG_TRACK_IN_POSTER);

        if ($this->version === 1) {
            $unpackFormat = 'Jcreation/Jmodification/Ntrack/a4reserved/Jduration/';
        } elseif ($this->version === 0) {
            $unpackFormat = 'Ncreation/Nmodification/Ntrack/a4reserved/Nduration/';
        } else {
            throw new ParserException("Version 0 or 1 expected but got \"{$this->version}\"");
        }

        $unpackFormat .= 'a8reserved/nlayer/nalternate/nvolume/a2reserved/H72matrix/Nwidth/Nheight';

        $read = $this->readHandle->read(80);
        if ($unpacked = unpack($unpackFormat, $read)) {
            try {
                $this->creationTime = DateTime::createFromSecondsSince1904($unpacked['creation']);
            } catch (Exception $e) {
                $this->creationTime = null;
            }
            try {
                $this->modificationTime = DateTime::createFromSecondsSince1904($unpacked['modification']);
            } catch (Exception $e) {
                $this->modificationTime = null;
            }
            $this->trackId = $unpacked['track'];
            $this->duration = $unpacked['duration'];
            $this->layer = Integer::unsignedIntToSignedInt($unpacked['layer'], 16);
            $this->alternateGroup = Integer::unsignedIntToSignedInt($unpacked['alternate'], 16);
            $this->volume = FixedPoint::createFromInt(Integer::unsignedIntToSignedInt($unpacked['volume'], 16), 8, 8);
            $this->matrix = Matrix::createWithHexString($unpacked['matrix']);
            $this->width = FixedPoint::createFromInt($unpacked['width'], 16, 16);
            $this->height = FixedPoint::createFromInt($unpacked['height'], 16, 16);
        } else {
            throw new ParserException('Cannot parse creationTime, modificationTime, timescale, duration');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        // Write in dependence to version
        if ($this->version === 1) {
            $this->writeHandle->write(pack('J', $this->creationTime ? $this->creationTime->getTimestamp1904() : 0));
            $this->writeHandle->write(pack('J', $this->modificationTime ? $this->modificationTime->getTimestamp1904() : 0));
            $this->writeHandle->write(pack('N', $this->trackId));
            $this->writeHandle->write(pack('x4'));
            $this->writeHandle->write(pack('J', $this->duration));
        } elseif ($this->version === 0) {
            $this->writeHandle->write(pack('N', $this->creationTime ? $this->creationTime->getTimestamp1904() : 0));
            $this->writeHandle->write(pack('N', $this->modificationTime ? $this->modificationTime->getTimestamp1904() : 0));
            $this->writeHandle->write(pack('N', $this->trackId));
            $this->writeHandle->write(pack('x4'));
            $this->writeHandle->write(pack('N', $this->duration));
        } else {
            throw new InvalidValueException("Version 0 or 1 expected but got \"{$this->version}\"");
        }

        // Reserved
        $this->writeHandle->write(pack('x8'));

        $this->writeHandle->write(pack('n', Integer::signedIntToUnsignedInt($this->layer, 16)));
        $this->writeHandle->write(pack('n', Integer::signedIntToUnsignedInt($this->alternateGroup, 16)));
        $this->writeHandle->write(pack('n', Integer::signedIntToUnsignedInt($this->volume->toInt(), 16)));

        // Reserved
        $this->writeHandle->write(pack('x2'));
        $this->writeHandle->write($this->matrix->toBytes());
        $this->writeHandle->write(pack('N', $this->width->toInt()));
        $this->writeHandle->write(pack('N', $this->height->toInt()));

        $this->writeChildren();
    }
}
