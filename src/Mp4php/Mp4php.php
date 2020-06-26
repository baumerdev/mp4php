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

namespace Mp4php;

use Exception;
use Mp4php\Box\Box;
use Mp4php\Box\FileTypeBox;
use Mp4php\Box\FontTableBox;
use Mp4php\Box\HandlerReferenceBox;
use Mp4php\Box\Itunes\AbstractItunesBox;
use Mp4php\Box\Itunes\ValueBox;
use Mp4php\Box\MetaBox;
use Mp4php\Box\MovieBox;
use Mp4php\Box\NameBox;
use Mp4php\Box\SubtitleSampleEntryBox;
use Mp4php\Box\TitleBox;
use Mp4php\Box\TrackReferenceBox;
use Mp4php\Box\TrackReferenceFollowSubtitle;
use Mp4php\Box\TrackReferenceTypeBox;
use Mp4php\Box\UserDataBox;
use Mp4php\Exceptions\CurrentlyUnsupportedException;
use Mp4php\Exceptions\FileWriteException;
use Mp4php\File\MP4ReadHandle;
use Mp4php\File\MP4WriteHandle;
use Mp4php\Frontend\Tracks;
use Mp4php\Track\AbstractTrack;

/**
 * Main public class for MP4PHP library
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mp4php
{
    /**
     * @var string
     */
    public $filename;
    /**
     * @var Box[]
     */
    public $boxes = [];

    /**
     * MP4Meta constructor with necessary filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Parse file's boxes
     */
    public function parse(): void
    {
        $readHandle = MP4ReadHandle::readingFile($this->filename);
        $this->boxes = BoxBuilder::parseBoxes($readHandle);
    }

    /**
     * Get track information
     *
     * @return AbstractTrack[]
     */
    public function getTrackInfo()
    {
        $movieBox = $this->getMovieBox();
        $tracks = new Tracks($movieBox->getMovieHeader()->getTimescale());

        return $tracks->trackInfo($movieBox->getTracks());
    }

    /**
     * Optimize file with better box structure for faster streaming
     *
     * @param string|null $targetFile Target file name for writing (if null, $this->filename will be overwritten)
     * @param int         $freeSpace  Optional free after moov (size in bytes; -1 to disable, 0 for automatic value)
     *
     * @throws Exception
     */
    public function optimize($targetFile = null, $freeSpace = 0): void
    {
        if ($targetFile !== null && file_exists($targetFile) || !is_writable(\dirname($targetFile))) {
            throw new FileWriteException("File $targetFile does exist or is not writable.");
        }

        $tmpFile = $targetFile === null ? $this->filename.'.tmp' : $targetFile.'.tmp';

        // Try optimizing and cancel on any exception
        try {
            $readHandle = MP4ReadHandle::readingFile($this->filename);
            $writeHandle = MP4WriteHandle::writingFile($tmpFile);
            //print_R($this);
            //$this->info();
            $optimize = new Optimize($this->boxes, $readHandle, $writeHandle);
            $optimize->setFreeSpace($freeSpace);
            $optimize->optimize();

            // Parse optimized file, to see if it raises any errors and to have the current box structure
            $readHandle = MP4ReadHandle::readingFile($tmpFile);
            $this->boxes = BoxBuilder::parseBoxes($readHandle);
            $this->info();
            //print_R($this);
        } catch (Exception $ex) {
            // Just clean up temp file if something did go wrong
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }

            throw $ex;
        }

        // Move temp file to final location
        if ($targetFile === null) {
            if (file_exists($this->filename)) {
                unlink($this->filename);
            }
            rename($tmpFile, $this->filename);
        } else {
            rename($tmpFile, $targetFile);
            $this->filename = $targetFile;
        }
    }

    /**
     * Save file (without any optimization)
     *
     * @param string|null $targetFile Target file name for writing (if null, $this->filename will be overwritten)
     *
     * @throws Exception
     */
    public function save(?string $targetFile = null): void
    {
        // @todo
        // Due to the complexity with the four overwrite scenarios, this is not yet supported
        if ($targetFile === null) {
            //throw new Exception('Overwriting files is not yet supported.');
        }

        if ($targetFile !== null && (file_exists($targetFile) || !is_writable(\dirname($targetFile)))) {
            throw new FileWriteException("File $targetFile does exist or is not writable.");
        }
        $tmpFile = null === $targetFile ? $this->filename.'.tmp' : $targetFile.'.tmp';

        try {
            $readHandle = MP4ReadHandle::readingFile($this->filename);
            $writeHandle = MP4WriteHandle::writingFile($tmpFile);

            $save = new Save($this->boxes, $readHandle, $writeHandle);
            $save->save($targetFile === null);
        } catch (Exception $ex) {
            // Just clean up temp file if something did go wrong
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }

            throw $ex;
        }

        // Move temp file to final location
        /*if ($targetFile === null) {
            if (file_exists($this->filename)) {
                unlink($this->filename);
            }
            rename($tmpFile, $this->filename);
        } else {*/
        rename($tmpFile, $targetFile);
        $this->filename = $targetFile;
        //}
    }

    /**
     * Call info() on all boxes
     */
    public function info(): void
    {
        foreach ($this->boxes as $box) {
            $box->info();
        }
    }

    /**
     * FFMPEG creates mostly "isom" branded files with minor version number 512 which in some rare cases can
     * create problems with iOS/macOS players that aren't able to detect eac3 audio tracks at all.
     *
     * @return bool Box found and content modified
     */
    public function changeISOMtoM4VHeader()
    {
        $fileTypeBox = $this->getFileTypeBox();

        if (preg_match('@^(?:isom|mp42|m4v )$@', $fileTypeBox->getMajorBrand()) && $fileTypeBox->getMinorVersion() === 512) {
            $fileTypeBox->setMajorBrand('M4V ');
            $fileTypeBox->setMinorVersion(0);
            $fileTypeBox->setCompatibleBrands([
                'M4V ',
                'M4A ',
                'mp42',
                'isom',
            ]);

            return true;
        }

        return false;
    }

    /**
     * Reset format in mov_text subtitles (e.g. ffmpeg sometime add "serif" font etc.)
     */
    public function movTextFormatReset(): void
    {
        $movieBox = $this->getMovieBox();

        foreach ($movieBox->getTracks() as $track) {
            if ($track->getMediaBox()->getHandlerReference()->getHandlerType() === HandlerReferenceBox::SUBTITLE) {
                $descriptionChildren = $track->getMediaBox()->getMediaInformation()->getSampleTable()->getSampleDescription()->getChildren() ?? [];
                foreach ($descriptionChildren as $descriptionChild) {
                    if (is_a($descriptionChild, SubtitleSampleEntryBox::class)) {
                        /* @var SubtitleSampleEntryBox $descriptionChild */
                        $this->movTextFormatResetSubtitleSample($descriptionChild);
                    }
                }
            }
        }
    }

    /**
     * @todo This method really needs some kind of search helper
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function fixAlternateGroupsAndTracks(array $audioTracks = [], array $subtitleTracks = []): void
    {
        $movie = $this->getMovieBox();

        $trackIndexIds = [];
        foreach ($movie->getTracks() as $trackIdx => $track) {
            $trackIndexIds[] = $track->getTrackHeader()->getTrackId();
        }

        $followSub = [];
        $forcedRef = [];
        foreach ($audioTracks as $audioTrackIndex => $audioTrack) {
            foreach ($subtitleTracks as $subtitleTrackIndex => $subtitleTrack) {
                if ($audioTrack['language'] === $subtitleTrack['language'] &&
                    (!isset($followSub[$audioTrackIndex]) || $subtitleTrack['forced'])) {
                    $followSub[$audioTrackIndex] = $trackIndexIds[$subtitleTrackIndex];
                }
            }
        }

        foreach ($subtitleTracks as $trackIndex => $subtitleTrack) {
            if (!empty($subtitleTrack['forced']) && $subtitleTrack['forced']) {
                foreach ($subtitleTracks as $trackIndex2 => $subtitleTrack2) {
                    if ($trackIndex !== $trackIndex2 &&
                        $subtitleTrack['language'] === $subtitleTrack2['language'] &&
                        (empty($subtitleTrack2['forced']) || !$subtitleTrack2['forced'])) {
                        $forcedRef[$trackIndex2] = $trackIndexIds[$trackIndex];
                    }
                }
            }
        }

        foreach ($movie->getTracks() as $trackIdx => $track) {
            $handlerType = $track->getMediaBox()->getHandlerReference()->getHandlerType();
            if ($handlerType === HandlerReferenceBox::VIDEO_TRACK) {
                $alternateGroup = 0;
            } elseif ($handlerType === HandlerReferenceBox::AUDIO_TRACK) {
                $alternateGroup = 1;

                if (isset($followSub[$trackIdx])) {
                    if (!$track->getTrackReference()) {
                        $trackReference = new TrackReferenceBox($track);
                        $trackReference->setModified();
                        $track->setTrackReference($trackReference);
                    }

                    if (!\is_array($track->getTrackReference()->getReferences()) || \count($track->getTrackReference()->getReferences()) < 1 || !is_a($track->getTrackReference()->getReferences()[0], TrackReferenceFollowSubtitle::class)) {
                        $forcedReference = new TrackReferenceFollowSubtitle($track->getTrackReference());
                        $forcedReference->setModified();
                        $forcedReference->setFollowTrackID($followSub[$trackIdx]);

                        $track->getTrackReference()->setReferences([
                            $forcedReference,
                        ]);
                    }
                }

                if (!empty($audioTracks[$trackIdx]['language'])) {
                    $track->getMediaBox()->getMediaHeader()->setLanguage($audioTracks[$trackIdx]['language']);
                }

                if (!empty($audioTracks[$trackIdx]['name'])) {
                    if (!$track->getUserData()) {
                        $track->setUserData(new UserDataBox($track));
                        $track->getUserData()->setModified();
                    }

                    /** @var NameBox|null $nameBox */
                    $nameBox = null;
                    /** @var TitleBox|null $titleBox */
                    $titleBox = null;

                    if (\is_array($track->getUserData()->getMeta())) {
                        foreach ($track->getUserData()->getMeta() as $box) {
                            if (is_a($box, NameBox::class)) {
                                $nameBox = $box;
                            } elseif (is_a($box, TitleBox::class)) {
                                $titleBox = $box;
                            }
                        }
                    }

                    if ($nameBox === null) {
                        $nameBox = new NameBox($track->getUserData());
                        $nameBox->setModified();
                    }

                    if (!$titleBox) {
                        $titleBox = new TitleBox($track->getUserData());
                        $titleBox->setModified();
                    }

                    $nameBox->setName($audioTracks[$trackIdx]['name']);
                    $titleBox->setLanguage('und');
                    $titleBox->setTitle($audioTracks[$trackIdx]['name']);

                    $track->getUserData()->setMeta([
                        $nameBox,
                        $titleBox,
                    ]);
                }
            } elseif ($handlerType === HandlerReferenceBox::SUBTITLE) {
                $alternateGroup = 2;
                if (!isset($subtitleTracks[$trackIdx])) {
                    continue;
                }

                if (!empty($subtitleTracks[$trackIdx]['language'])) {
                    $track->getMediaBox()->getMediaHeader()->setLanguage($subtitleTracks[$trackIdx]['language']);
                }

                if (!empty($subtitleTracks[$trackIdx]['name'])) {
                    if (!$track->getUserData()) {
                        $track->setUserData(new UserDataBox($track));
                        $track->getUserData()->setModified();
                    }

                    /** @var NameBox|null $nameBox */
                    $nameBox = null;
                    /** @var TitleBox|null $titleBox */
                    $titleBox = null;

                    if (\is_array($track->getUserData()->getMeta())) {
                        foreach ($track->getUserData()->getMeta() as $box) {
                            if (is_a($box, NameBox::class)) {
                                $nameBox = $box;
                            } elseif (is_a($box, TitleBox::class)) {
                                $titleBox = $box;
                            }
                        }
                    }

                    if (!$nameBox) {
                        $nameBox = new NameBox($track->getUserData());
                        $nameBox->setModified();
                    }

                    if (!$titleBox) {
                        $titleBox = new TitleBox($track->getUserData());
                        $titleBox->setModified();
                    }

                    $nameBox->setName($subtitleTracks[$trackIdx]['name']);
                    $titleBox->setLanguage('und');
                    $titleBox->setTitle($subtitleTracks[$trackIdx]['name']);

                    $track->getUserData()->setMeta([
                        $nameBox,
                        $titleBox,
                    ]);
                }

                $descriptionChildren = $track->getMediaBox()->getMediaInformation()->getSampleTable()->getSampleDescription()->getChildren() ?? [];
                foreach ($descriptionChildren as $descriptionChild) {
                    if (is_a($descriptionChild, SubtitleSampleEntryBox::class)) {
                        /* @var SubtitleSampleEntryBox $descriptionChild */
                        if ($subtitleTracks[$trackIdx]['forced']) {
                            $descriptionChild->setSamplesAreForced(SubtitleSampleEntryBox::ALL_SAMPLES_FORCED);
                        } elseif (!$subtitleTracks[$trackIdx]['forced']) {
                            $descriptionChild->setSamplesAreForced(SubtitleSampleEntryBox::NO_SAMPLES_FORCED);
                        }
                        break;
                    }
                }

                if (isset($forcedRef[$trackIdx])) {
                    if (!$track->getTrackReference()) {
                        $trackReference = new TrackReferenceBox($track);
                        $trackReference->setModified();
                        $track->setTrackReference($trackReference);
                    }

                    if (!\is_array($track->getTrackReference()->getReferences()) || \count($track->getTrackReference()->getReferences()) < 1 || !is_a($track->getTrackReference()->getReferences()[0], TrackReferenceTypeBox::class)) {
                        $forcedReference = new TrackReferenceTypeBox($track->getTrackReference(), TrackReferenceTypeBox::TYPE_FORC);
                        $forcedReference->setModified();
                        $forcedReference->setTrackIds([$forcedRef[$trackIdx]]);
                        $track->getTrackReference()->setReferences([
                            $forcedReference,
                        ]);
                    }
                }
            } else {
                $alternateGroup = 3;
            }

            $track->getTrackHeader()->setAlternateGroup($alternateGroup);

            //$track->info();
        }
    }

    /**
     * Set MP4 name in Metadata
     *
     * @todo This method really needs some kind of search helper
     *
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setName(string $name): void
    {
        $movieBox = $this->getMovieBox();
        if (!$movieBox) {
            throw new Exception('No movie box "moov" found.');
        }

        $userData = $movieBox->getUserData();
        if (!$userData) {
            throw new CurrentlyUnsupportedException('No user data box "udta" found. Creating one is not yet supported.');
        }

        $meta = $userData->getMeta();
        if (!$meta || \count($meta) !== 1 || $meta[0]->getType() !== 'meta') {
            throw new CurrentlyUnsupportedException('No metadata data box "meta" found. Creating one is not yet supported.');
        }

        /** @var MetaBox $firstMeta */
        $firstMeta = $meta[0];
        $metadata = $firstMeta->getMetadata();

        foreach ($metadata->getChildren() as $child) {
            if (!is_a($child, ValueBox::class) || utf8_encode($child->getType()) !== AbstractItunesBox::NAME || \count($child->getData()) !== 1) {
                continue;
            }

            /** @var ValueBox $child */
            $data = $child->getData();

            $data[0]->value = $name;
            $child->setData($data);

            return;
        }

        throw new CurrentlyUnsupportedException('No name metadata found. Creating one is not yet supported.');
    }

    /**
     * Return Movie Box (type 'moov') if existing
     *
     * @return MovieBox|null
     */
    protected function getMovieBox()
    {
        foreach ($this->boxes as $box) {
            if (is_a($box, MovieBox::class)) {
                /* @var MovieBox $box */

                return $box;
            }
        }

        return null;
    }

    /**
     * Return FileTypeBox Box (type 'ftyp') if existing
     *
     * @return FileTypeBox|null
     */
    protected function getFileTypeBox()
    {
        foreach ($this->boxes as $box) {
            if (is_a($box, FileTypeBox::class)) {
                /* @var FileTypeBox $box */

                return $box;
            }
        }

        return null;
    }

    /**
     * Reset format in mov_text subtitles in SubtitleSampleEntryBox
     *
     * @param SubtitleSampleEntryBox $sampleEntryBox
     */
    protected function movTextFormatResetSubtitleSample($sampleEntryBox): void
    {
        $styleRecord = $sampleEntryBox->getStyleRecord();
        $styleRecord->fontSize = 40;
        $sampleEntryBox->setStyleRecord($styleRecord);

        $entryChildren = $sampleEntryBox->getChildren() ?? [];
        foreach ($entryChildren as $entryChild) {
            if ($entryChild instanceof FontTableBox) {
                $replaceFontRecords = [];
                foreach ($entryChild->getFontRecords() as $fontRecord) {
                    if ($fontRecord->fontName !== 'Sans-Serif') {
                        $fontRecord->fontName = 'Sans-Serif';
                    }
                    $replaceFontRecords[] = $fontRecord;
                }

                $entryChild->setFontRecords($replaceFontRecords);
            }
        }
    }
}
