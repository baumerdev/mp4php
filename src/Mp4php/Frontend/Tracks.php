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

namespace Mp4php\Frontend;

use Mp4php\Box\AbstractSampleEntryBox;
use Mp4php\Box\AudioSampleEntryBox;
use Mp4php\Box\Codec\CodecDAC3Box;
use Mp4php\Box\HandlerReferenceBox;
use Mp4php\Box\SubtitleSampleEntryBox;
use Mp4php\Box\TitleBox;
use Mp4php\Box\TrackBox;
use Mp4php\Box\TrackReferenceChapterList;
use Mp4php\Box\TrackReferenceFollowSubtitle;
use Mp4php\Track\AudioTrack;
use Mp4php\Track\OtherTrack;
use Mp4php\Track\SubtitleTrack;
use Mp4php\Track\VideoTrack;

/**
 * Converting track data from and to frontend interface data
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tracks
{
    /**
     * @var int
     */
    protected $timescale;

    /**
     * Tracks constructor.
     *
     * @param int $timescale
     */
    public function __construct($timescale)
    {
        $this->timescale = $timescale;
    }

    /**
     * Return simple track info for frontend display
     *
     * @return array
     */
    public function trackInfo(array $trackBoxes)
    {
        $tracks = [];
        $chapterTrackIds = [];
        /** @var TrackBox $trackBox */
        foreach ($trackBoxes as $trackIndex => $trackBox) {
            $trackId = $trackBox->getTrackHeader()->getTrackId();

            if (\in_array($trackId, $chapterTrackIds)) {
                continue;
            }

            $handlerType = $trackBox->getMediaBox()->getHandlerReference()->getHandlerType();
            if ($handlerType === HandlerReferenceBox::VIDEO_TRACK) {
                $track = new VideoTrack();
                $track->width = "{$trackBox->getTrackHeader()->getWidth()}";
                $track->height = "{$trackBox->getTrackHeader()->getHeight()}";

                $chapterTrackIds = array_merge($chapterTrackIds, $this->chapterTrackReferences($trackBox));
            } elseif ($handlerType === HandlerReferenceBox::AUDIO_TRACK) {
                $track = new AudioTrack();
                $track->volume = "{$trackBox->getTrackHeader()->getVolume()}";
                $track->subtitle = $this->subtitleTrackReferences($trackBox);
                $track->bitrate = $this->trackBitrate($trackBox);
                $track->channels = $this->audioTrackChannels($trackBox);
                $track->sampleRate = $this->audioTrackSamplerate($trackBox);
            } elseif ($handlerType === HandlerReferenceBox::SUBTITLE) {
                $track = new SubtitleTrack();
                $track->forced = $this->subtitleTrackForced($trackBox);
            } else {
                $track = new OtherTrack();
            }

            $track->trackIndex = $trackIndex;
            $track->trackID = $trackId;
            $track->enabled = $trackBox->getTrackHeader()->isTrackEnabled();
            $track->alternateGroup = $trackBox->getTrackHeader()->getAlternateGroup();
            $track->language = $trackBox->getMediaBox()->getMediaHeader()->getLanguage();
            $track->title = $this->trackTitle($trackBox);
            $track->format = $this->trackFormat($trackBox);
            $track->duration = $this->trackDuration($trackBox);

            $tracks[] = $track;
        }

        return $tracks;
    }

    /**
     * Parse track title
     *
     * @return string|null
     */
    protected function trackTitle(TrackBox $trackBox)
    {
        if ($trackBox->getUserData() && \is_array($trackBox->getUserData()->getChildren())) {
            foreach ($trackBox->getUserData()->getChildren() as $userDataChild) {
                if (is_a($userDataChild, TitleBox::class)) {
                    /* @var TitleBox $userDataChild */

                    return $userDataChild->getTitle();
                }
            }
        }

        return null;
    }

    /**
     * Get first Sample Description Entry
     */
    protected function firstSampleDescription(TrackBox $trackBox): ?AbstractSampleEntryBox
    {
        $sampleDescription = $trackBox->getMediaBox()->getMediaInformation()->getSampleTable()->getSampleDescription();
        $children = $sampleDescription->getChildren();
        if (\is_array($children) && isset($children[0]) && $children[0] instanceof AbstractSampleEntryBox) {
            return $children[0];
        }

        return null;
    }

    /**
     * Get track format (box type)
     *
     * @return string|null
     */
    protected function trackFormat(TrackBox $trackBox)
    {
        if ($sampleBox = $this->firstSampleDescription($trackBox)) {
            return $sampleBox->getType();
        }

        return null;
    }

    /**
     * Check if subtitle samples are forced
     *
     * @return string|null null or string no|some|all
     */
    protected function subtitleTrackForced(TrackBox $trackBox)
    {
        $sampleBox = $this->firstSampleDescription($trackBox);
        if ($sampleBox && is_a($sampleBox, SubtitleSampleEntryBox::class)) {
            /* @var SubtitleSampleEntryBox $sampleBox */

            return $sampleBox->samplesAreForced();
        }

        return null;
    }

    /**
     * Parse chapter track references
     *
     * @return array [int] Track IDs
     */
    protected function chapterTrackReferences(TrackBox $trackBox)
    {
        $chapterTrackIds = [];
        if ($trackBox->getTrackReference() &&
            \is_array($trackBox->getTrackReference()->getReferences()) &&
            isset($trackBox->getTrackReference()->getReferences()[0]) &&
            is_a($trackBox->getTrackReference()->getReferences()[0], TrackReferenceChapterList::class)
        ) {
            /** @var TrackReferenceChapterList $chapterListReference */
            $chapterListReference = $trackBox->getTrackReference()->getReferences()[0];
            $chapterTrackIds[] = $chapterListReference->getChapterTrackID();
            if ($chapterListReference->getScreenshotTrackID()) {
                $chapterTrackIds[] = $chapterListReference->getScreenshotTrackID();
            }
        }

        return $chapterTrackIds;
    }

    /**
     * Parse subtitle track reference
     *
     * @return int|null
     */
    protected function subtitleTrackReferences(TrackBox $trackBox)
    {
        if ($trackBox->getTrackReference() &&
            \is_array($trackBox->getTrackReference()->getReferences()) &&
            isset($trackBox->getTrackReference()->getReferences()[0]) &&
            is_a($trackBox->getTrackReference()->getReferences()[0], TrackReferenceFollowSubtitle::class)
        ) {
            /** @var TrackReferenceFollowSubtitle $subtitleReference */
            $subtitleReference = $trackBox->getTrackReference()->getReferences()[0];

            return $subtitleReference->getFollowTrackID();
        }

        return null;
    }

    /**
     * Calculate duration in seconds with timescale
     *
     * @return float|null
     */
    protected function trackDuration(TrackBox $trackBox)
    {
        if ($trackBox->getTrackHeader()->getDuration() && $this->timescale !== 0) {
            return $trackBox->getTrackHeader()->getDuration() / $this->timescale;
        }

        return null;
    }

    /**
     * Get track's bitrate
     */
    protected function trackBitrate(TrackBox $trackBox): ?int
    {
        $sampleBox = $this->firstSampleDescription($trackBox);
        if ($sampleBox === null) {
            return null;
        }
        if ($sampleBox->getType() !== 'ac-3') {
            return null;
        }
        if (!isset($sampleBox->getChildren()[0])) {
            return null;
        }
        $firstChild = $sampleBox->getChildren()[0];
        if ($firstChild instanceof CodecDAC3Box) {
            return $firstChild->getBitrate();
        }

        return null;
    }

    /**
     * Get channel configuration
     */
    protected function audioTrackSamplerate(TrackBox $trackBox): ?string
    {
        if ($sampleBox = $this->firstSampleDescription($trackBox)) {
            if ($sampleBox instanceof AudioSampleEntryBox) {
                return (string) $sampleBox->getSampleRate();
            }
        }

        return null;
    }

    /**
     * Get channel configuration
     */
    protected function audioTrackChannels(TrackBox $trackBox): ?string
    {
        if ($sampleBox = $this->firstSampleDescription($trackBox)) {
            if ($sampleBox->getType() === 'ac-3') {
                if (isset($sampleBox->getChildren()[0]) && $sampleBox->getChildren()[0]->getType() === 'dac3') {
                    $dac3 = $sampleBox->getChildren()[0];
                    if (is_a($dac3, CodecDAC3Box::class)) {
                        /* @var CodecDAC3Box $dac3 */
                        return $this->audioTrackChannelsDAC3($dac3);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get channel configuration for AC3
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function audioTrackChannelsDAC3(CodecDAC3Box $dac3)
    {
        $channels = $dac3->getChannelLayout();

        if ($channels === CodecDAC3Box::CHANNEL_1_PLUS_1 ||
            $channels === CodecDAC3Box::CHANNEL_MONO ||
            $channels === CodecDAC3Box::CHANNEL_STEREO
        ) {
            return $channels.($dac3->isLfeOn() ? '.1' : '');
        }

        if (preg_match('@(\d)/(\d)@', $channels, $match)) {
            return ($match[1] + $match[2]).'.'.($dac3->isLfeOn() ? '1' : '0');
        }

        return $channels;
    }
}
