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
use Mp4php\Exceptions\ParserException;

/**
 * Movie Header Box (type 'mvhd')
 *
 * aligned(8) class MovieHeaderBox extends FullBox(‘mvhd’, version, 0) {
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
 *     template int(32) rate = 0x00010000; // typically 1.0
 *     template int(16) volume = 0x0100;
 *     // typically, full volume
 *     const bit(16) reserved = 0;
 *     const unsigned int(32)[2] reserved = 0;
 *     template int(32)[9] matrix =
 *         { 0x00010000,0,0,0,0x00010000,0,0,0,0x40000000 };
 *         // Unity matrix
 *     bit(32)[6] pre_defined = 0;
 *     unsigned int(32) next_track_ID;
 * }
 */
class MovieHeaderBox extends AbstractFullBox
{
    const TYPE = 'mvhd';

    protected $boxImmutable = false;

    protected $container = [MovieBox::class];

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
     * @var FixedPoint 16.16
     */
    protected $rate;
    /**
     * @var FixedPoint 8.8
     */
    protected $volume;
    /**
     * @var Matrix
     */
    protected $matrix;
    /**
     * @var int
     */
    protected $nextTrackId;

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

    public function getRate(): FixedPoint
    {
        return $this->rate;
    }

    public function setRate(FixedPoint $rate): void
    {
        if ((string) $this->rate === (string) $rate) {
            return;
        }

        $this->rate = $rate;
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

    public function getNextTrackId(): int
    {
        return $this->nextTrackId;
    }

    public function setNextTrackId(int $nextTrackId): void
    {
        if ($this->nextTrackId === $nextTrackId) {
            return;
        }

        $this->nextTrackId = $nextTrackId;
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
        } elseif ($this->version === 0) {
            $unpackFormat = 'Ncreation/Nmodification/Ntimescale/Nduration/';
        } else {
            throw new ParserException(sprintf('Version 0 or 1 expected but got %d', $this->version));
        }

        $unpackFormat .= 'Nrate/nvolume/a2reservedBit/N2reservedInt/H72matrix/a24preDefined/NnextTrack';

        $read = $this->readHandle->read(96);
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
            $this->timescale = $unpacked['timescale'];
            $this->duration = $unpacked['duration'];
            $this->rate = FixedPoint::createFromInt(Integer::unsignedIntToSignedInt($unpacked['rate'], 32), 16, 16);
            $this->volume = FixedPoint::createFromInt(Integer::unsignedIntToSignedInt($unpacked['volume'], 16), 8, 8);
            $this->matrix = Matrix::createWithHexString($unpacked['matrix']);
            $this->nextTrackId = $unpacked['nextTrack'];
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

        // Rate
        $this->writeHandle->write(pack('N', Integer::signedIntToUnsignedInt($this->rate->toInt(), 32)));

        // Volume
        $this->writeHandle->write(pack('n', Integer::signedIntToUnsignedInt($this->volume->toInt(), 16)));

        // Reserved bits
        $this->writeHandle->write(pack('x2'));

        // Reserved int
        $this->writeHandle->write(pack('N', 0));
        $this->writeHandle->write(pack('N', 0));

        // Matrix
        $this->writeHandle->write($this->matrix->toBytes());

        // Pre defined
        $this->writeHandle->write(pack('x24'));

        // Next track
        $this->writeHandle->write(pack('N', $this->nextTrackId));
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
