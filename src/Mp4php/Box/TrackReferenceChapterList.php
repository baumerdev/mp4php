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

use Mp4php\Exceptions\ParserException;

/**
 * Track Reference Chapter List (type 'chap')
 */
class TrackReferenceChapterList extends AbstractTrackReferenceTypeBox
{
    const TYPE = 'chap';

    protected $boxImmutable = false;

    /**
     * @var int
     */
    protected $chapterTrackID;
    /**
     * @var int|null
     */
    protected $screenshotTrackID;

    public function getChapterTrackID(): int
    {
        return $this->chapterTrackID;
    }

    public function setChapterTrackID(int $chapterTrackID): void
    {
        if ($this->chapterTrackID === $chapterTrackID) {
            return;
        }

        $this->chapterTrackID = $chapterTrackID;
        $this->setModified();
    }

    public function getScreenshotTrackID(): ?int
    {
        return $this->screenshotTrackID;
    }

    public function setScreenshotTrackID(?int $screenshotTrackID): void
    {
        if ($this->screenshotTrackID === $screenshotTrackID) {
            return;
        }

        $this->screenshotTrackID = $screenshotTrackID;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        $unpacked = unpack('N*track', $this->readHandle->read($this->remainingBytes() === 4 ? 4 : 8));
        if ($unpacked) {
            if (isset($unpacked['track1'])) {
                $this->chapterTrackID = $unpacked['track1'];
            }
            if (isset($unpacked['track2'])) {
                $this->screenshotTrackID = $unpacked['track2'];
            }

            parent::parse();
        } else {
            throw new ParserException('Cannot parse chapter track ID.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writeModifiedContent(): void
    {
        $this->writeHandle->write(pack('N', $this->chapterTrackID));

        if ($this->screenshotTrackID) {
            $this->writeHandle->write(pack('N', $this->screenshotTrackID));
        }

        parent::writeModifiedContent();
    }
}
