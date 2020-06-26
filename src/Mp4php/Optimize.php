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

use Mp4php\Box\Box;
use Mp4php\Box\ChunkLargeOffsetBox;
use Mp4php\Box\ChunkOffsetBox;
use Mp4php\Box\FileTypeBox;
use Mp4php\Box\FreeSpaceBox;
use Mp4php\Box\MediaDataBox;
use Mp4php\Box\MediaDataCombinedBox;
use Mp4php\Box\MovieBox;
use Mp4php\Exceptions\SizeException;
use Mp4php\Exceptions\StructureException;
use Mp4php\Exceptions\UnsupportedFormatException;
use Mp4php\File\MP4ReadHandle;
use Mp4php\File\MP4WriteHandle;
use OutOfRangeException;

/**
 * Create a new file re-ordering boxes into a qt-faststart order with moov box at second position and mdat
 * at the last. Also add (optionally) approx. 1 MB of free space after moov box to be prepared for adding
 * longer metadata without destroying the optimized file structure
 *
 * ftyp - moov - free - mdat
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Optimize
{
    const BYTES_4GB = 4294967296;

    /**
     * Add no Free Space Box. But there is a chance when conversion between from 64bit to 32bit chunks is in effect
     * that there will be a Free Space Box nonetheless.
     */
    const FREE_SPACE_DISABLE = -1;
    /** Calculate size of Free Space Box automatically */
    const FREE_SPACE_AUTO = 0;

    /**
     * @var array
     */
    protected $boxes;
    /**
     * @var MP4ReadHandle
     */
    protected $readHandle;
    /**
     * @var MP4WriteHandle
     */
    protected $writeHandle;

    /**
     * @var int
     */
    protected $freeSpace = self::FREE_SPACE_AUTO;

    /**
     * Optimize with boxes array and handle for read/write
     */
    public function __construct(array $boxes, MP4ReadHandle $readHandle, MP4WriteHandle $writeHandle)
    {
        $this->boxes = $boxes;
        $this->readHandle = $readHandle;
        $this->writeHandle = $writeHandle;
    }

    /**
     * Size for free space puffer Box
     * 0 - if set to Optimize::FREE_SPACE_DISABLE
     * 10% of total file size if Optimize::FREE_SPACE_AUTO (min. 100KiB, max. 1MiB)
     * or value manually set with setFreeSpace()
     */
    public function getFreeSpace(): int
    {
        if ($this->freeSpace === self::FREE_SPACE_DISABLE) {
            return 0;
        }

        if ($this->freeSpace !== self::FREE_SPACE_AUTO) {
            return $this->freeSpace;
        }

        $freeSpace = $this->totalSize() * 0.1;
        if ($freeSpace > 1024 * 1024) {
            return 1024 * 1024;
        }
        if ($freeSpace < 1024 * 100) {
            return 1024 * 100;
        }

        return (int) $freeSpace;
    }

    /**
     * Set free space, can be Optimize::FREE_SPACE_* or any positive int
     */
    public function setFreeSpace(int $freeSpace): void
    {
        if ($this->freeSpace === self::FREE_SPACE_DISABLE ||
            $this->freeSpace === self::FREE_SPACE_AUTO ||
            $this->freeSpace > 0) {
            $this->freeSpace = $freeSpace;

            return;
        }

        throw new OutOfRangeException('Free space can either be Optimize::FREE_SPACE_DISABLE, Optimize::FREE_SPACE_AUTO or any int > 0');
    }

    /**
     * Optimize box structure (ftyp moov [free] mdat) and write
     */
    public function optimize(): void
    {
        // Reorder boxes the optimal way
        $this->optimalBoxOrder();

        // Remember movie box
        /* @var MovieBox $movieBox */
        $movieBox = null;

        /** @var Box $box */
        foreach ($this->boxes as $box) {
            $box->setReadHandle($this->readHandle);
            $box->setWriteHandle($this->writeHandle);

            if (is_a($box, MovieBox::class)) {
                /** @var MovieBox $movieBox */
                $movieBox = $box;

                // Remember offset
                $offsetBeforeMovieBox = $this->writeHandle->offset();

                // Write the complete Movie box to know it's exact size
                $box->write();

                // If optional free space should be added after moov,
                // move write handles pointer accordingly
                $freeSpaceSize = $this->getFreeSpace();
                if ($freeSpaceSize > 0) {
                    // Add 8 bits for box type and size
                    $this->writeHandle->seekBy($freeSpaceSize + 8);
                }

                // We now know the offset of mdat and we know the size of mdat
                // so we know the final size of the file and can decide whether to use
                // stco or co64 chunk offset size boxes
                $finalSize = $this->writeHandle->offset() + $this->getMediaDataBox()->getSize();
                $changedSize = $this->updateTrackChunkOffsetBoxes($movieBox, $finalSize > 4294967295);
                if ($changedSize !== 0) {
                    $this->writeHandle->seekBy($changedSize);
                }

                // Update all the chunk offsets
                $this->updateMovieBoxTrackChunks($movieBox);

                // Go back to offset before first movie box write
                // and write the updated movie box version
                $this->writeHandle->seek($offsetBeforeMovieBox);
                $box->write();

                // Add an optional free space box afterwards
                if ($freeSpaceSize > 0) {
                    $freeBox = new FreeSpaceBox();
                    $freeBox->constructDefault($freeSpaceSize + 8);
                    $freeBox->setReadHandle($this->readHandle);
                    $freeBox->setWriteHandle($this->writeHandle);
                    $freeBox->write();
                    $freeBox->info(false);
                }
            } elseif (is_a($box, MediaDataBox::class)) {
                if (!$movieBox) {
                    throw new StructureException('Movie Box has to be defined before Media Data Box.');
                }
                $box->write();
                $box->info(false);
            } else {
                $box->write();
                $box->info(false);
            }
        }
    }

    /**
     * Get optimal box order (ftyp, moov[, other-than-free-boxes], combined-mdat)
     */
    protected function optimalBoxOrder(): void
    {
        $optimalOrderedBoxes = [];

        $mdat = new MediaDataCombinedBox();
        /** @var Box $box */
        foreach ($this->boxes as $box) {
            if ($box->getType() === FileTypeBox::TYPE) {
                array_unshift($optimalOrderedBoxes, $box);
            } elseif ($box->getType() === MovieBox::TYPE) {
                $addBoxes = [$box];
                array_splice($optimalOrderedBoxes, 1, 0, $addBoxes);
            } elseif ($box instanceof MediaDataBox) {
                $mdat->addMediaDataBox($box);
            } elseif ($box->getType() !== FreeSpaceBox::TYPE) {
                throw new UnsupportedFormatException(sprintf('Unable to handle box of type "%s" at root level.', $box->getType()));
            }
        }
        $optimalOrderedBoxes[] = $mdat;

        $this->boxes = $optimalOrderedBoxes;

        $this->fixOffsets();
    }

    /**
     * Fix offsets on all boxes
     */
    protected function fixOffsets(): void
    {
        // Get mdat offset
        $offset = 0;
        /** @var Box $box */
        foreach ($this->boxes as $box) {
            $box->setOffset($offset);
            $offset += $box->getSize();
        }
    }

    /**
     * Total size of all boxes
     */
    protected function totalSize(): int
    {
        /** @var Box $lastBox */
        $lastBox = $this->boxes[\count($this->boxes) - 1];

        return $lastBox->getOffset() + $lastBox->getSize();
    }

    /**
     * Get media data box from boxes array
     */
    protected function getMediaDataBox(): ?MediaDataCombinedBox
    {
        foreach ($this->boxes as $box) {
            /** @var MediaDataCombinedBox $box */
            if (is_a($box, MediaDataCombinedBox::class)) {
                return $box;
            }
        }

        return null;
    }

    /**
     * Update all movie box's track's chunks with correct chunk offset sized box (stco or co64)
     *
     * This doesn't change the offsets; this must be done in a second step after the moov box
     * is written and we know its final size for sure
     *
     * @throws SizeException
     *
     * @return int Size change in bytes
     */
    protected function updateTrackChunkOffsetBoxes(MovieBox $box, bool $co64Needed): int
    {
        $sizeChange = 0;

        foreach ($box->getTracks() as $track) {
            $sampleTable = $track->getMediaBox()->getMediaInformation()->getSampleTable();
            $chunkOffset = $sampleTable->getChunkOffset();

            // If we already have the correct size we don't need to change anything here
            if ((\get_class($chunkOffset) === ChunkOffsetBox::class && !$co64Needed) ||
                (\get_class($chunkOffset) === ChunkLargeOffsetBox::class && $co64Needed)) {
                continue;
            }

            // Create a new box according to the needed offset size
            if ($co64Needed) {
                $sizeChangePerEntry = 4;
                $newChunkOffset = new ChunkLargeOffsetBox($sampleTable);
            } else {
                $sizeChangePerEntry = -4;
                $newChunkOffset = new ChunkOffsetBox($sampleTable);
            }

            // Set new boxes data from the original box
            $newChunkOffset->setReadHandle($this->readHandle);
            $newChunkOffset->setWriteHandle($this->writeHandle);
            $newChunkOffset->setModified();
            $newChunkOffset->setOffset($chunkOffset->getOffset());
            $newChunkOffset->updateVersionAndFlags($chunkOffset->getVersion(), $chunkOffset->getFlags());

            $sizeChange += $sizeChangePerEntry * $chunkOffset->getEntryCount();

            // Add entries and replace the box in the parent sample table
            $newChunkOffset->setEntries($chunkOffset->entries());
            $sampleTable->setChunkOffset($newChunkOffset);
        }

        return $sizeChange;
    }

    /**
     * Update all movie box's track's chunk offsets for new offset
     *
     * It's assumed that the writeHandle's current offset is the designated 'mdat' boxes offset
     * while the current (parsed) MediaDataBox's offset is the old offset. The difference is then
     * added to (or subtracted from) each track's chunk offset entries.
     */
    protected function updateMovieBoxTrackChunks(MovieBox $box): void
    {
        // Get media data box for later calculating new data offsets
        $mediaDataBox = $this->getMediaDataBox();

        // Get the old offset of mdat box and current write offset assuming this is the new mdat box offset
        $oldOffset = $mediaDataBox->getOffset();
        $currentOffset = $this->writeHandle->offset();

        $offsetDifference = $currentOffset - $oldOffset;

        foreach ($box->getTracks() as $track) {
            $sampleTable = $track->getMediaBox()->getMediaInformation()->getSampleTable();
            $chunkOffset = $sampleTable->getChunkOffset();
            $chunkEntries = $chunkOffset->entries();
            foreach ($chunkEntries as $entryIdx => $entryOffset) {
                $newOffset = $mediaDataBox->newOffset($entryOffset, $offsetDifference);
                if ($newOffset === null) {
                    throw new SizeException("Unable to calculate new offset for old offset $entryOffset.");
                }
                if ($newOffset >= static::BYTES_4GB && \get_class($chunkOffset) !== ChunkLargeOffsetBox::class) {
                    throw new SizeException("Cannot store offset $newOffset in 32 bit 'stco' box.");
                }
                $chunkEntries[$entryIdx] = $newOffset;
            }
            $chunkOffset->setEntries($chunkEntries);
        }
    }
}
