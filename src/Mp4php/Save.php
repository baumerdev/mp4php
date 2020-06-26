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
use Mp4php\Box\FreeSpaceBox;
use Mp4php\Box\MovieBox;
use Mp4php\Exceptions\InvalidValueException;
use Mp4php\File\MP4MemoryWriteHandle;
use Mp4php\File\MP4ReadHandle;
use Mp4php\File\MP4WriteHandle;
use RuntimeException;

/**
 * Save MP4 file without optimization.
 *
 * The original "moov" box may be overwritten with a "free" box and the new "moov" box will be appended at file's end if
 * - An existing file is being overwritten
 * - The "moov" box isn't already the last box
 * - The size of the new "moov" box exceeds the size of the original "moov" box (and any directly following "free" box)
 */
class Save
{
    const SCENARIO_OVERWRITE = 0;
    const SCENARIO_OVERWRITE_TRUNCATE_FILE = 1;
    const SCENARIO_OVERWRITE_RESIZE_FREE = 2;
    const SCENARIO_REPLACE_FREE_MOVE_TO_END = 3;

    /**
     * @var Box[]
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
     * Optimize with boxes array and handle for read/write
     */
    public function __construct(array $boxes, MP4ReadHandle $readHandle, MP4WriteHandle $writeHandle)
    {
        $this->boxes = $boxes;
        $this->readHandle = $readHandle;
        $this->writeHandle = $writeHandle;
    }

    /**
     * Save data to file
     *
     * @param bool $overwrite Overwrite existing file partially
     */
    public function save($overwrite = false): void
    {
        if ($overwrite) {
            $this->overwrite();
        } else {
            $this->writeNew();
        }
    }

    /**
     * Save content by overwriting parts in existing file
     */
    protected function overwrite(): void
    {
        /** @var MovieBox $movieBox */
        /** @var FreeSpaceBox $nextBox */
        [$movieBox, $nextBox] = $this->getMovieAndFollowingBox();

        // Box hasn't changed, so nothing more to do
        if (!$movieBox->isModified()) {
            return;
        }

        // Get the new Movie Box as string
        $newMoovContent = $this->newMovieBoxContent($movieBox);

        // Calculate size diff between new and old Movie Box versions
        $sizeDiff = \strlen($newMoovContent) - $movieBox->getSize();

        // Continue depending which scenario suits best for writing
        switch ($this->scenario($sizeDiff, $nextBox)) {
            case self::SCENARIO_OVERWRITE:
                $this->replaceMovieBoxWithContent($movieBox, $newMoovContent);
                break;
            case self::SCENARIO_OVERWRITE_TRUNCATE_FILE:
                $this->replaceMovieBoxWithContent($movieBox, $newMoovContent);
                $this->truncateFileAtOffset($movieBox->getOffset() + \strlen($newMoovContent));
                break;
            case self::SCENARIO_OVERWRITE_RESIZE_FREE:
                $this->replaceMovieBoxWithContentResizeFree($movieBox, $newMoovContent, $nextBox, $sizeDiff);
                break;
            case self::SCENARIO_REPLACE_FREE_MOVE_TO_END:
                $this->replaceMovieBoxWithFreeBox($movieBox);
                $this->appendMovieBoxContentToFile($newMoovContent);
                break;
        }
    }

    /**
     * Save content by writing boxes to new file without any changes or optimization
     */
    protected function writeNew(): void
    {
        foreach ($this->boxes as $box) {
            $box->setReadHandle($this->readHandle);
            $box->setWriteHandle($this->writeHandle);

            $box->write();
            $box->info(false);
        }
    }

    /**
     * Get array with MovieBox and following box if any
     * [ MovieBox, Box (if any) ]
     */
    protected function getMovieAndFollowingBox(): ?array
    {
        $boxCount = \count($this->boxes);
        for ($boxIndex = 0; $boxIndex < $boxCount; ++$boxIndex) {
            $box = $this->boxes[$boxIndex];

            if (is_a($box, MovieBox::class)) {
                return [
                    $box,
                    $this->boxes[$boxIndex + 1] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function scenario(int $sizeDiff, ?Box $nextBox = null): int
    {
        // Size hasn't changed or Box increased but it's the last box:
        // Just write the boxes content
        if ($sizeDiff === 0 ||
            $sizeDiff > 0 && $nextBox === null) {
            return self::SCENARIO_OVERWRITE;
        }

        // Box increased and we have enough space in following FreeSpaceBox:
        // Write this MovieBox and decrease FreeSpaceBox
        if ($sizeDiff > 0 &&
            $nextBox &&
            is_a($nextBox, FreeSpaceBox::class) &&
            $sizeDiff < $nextBox->getSize() - 8) {
            return self::SCENARIO_OVERWRITE_RESIZE_FREE;
        }

        // Box increased and there is no following FreeSpaceBox with enough space:
        // Replace MovieBox at its original offset with FreeSpaceBox and
        // append MovieBox to the end of the file
        if ($sizeDiff > 0) {
            return self::SCENARIO_REPLACE_FREE_MOVE_TO_END;
        }

        // Box decreased and it's the last box:
        // Truncate the file
        if (!$nextBox) {
            return self::SCENARIO_OVERWRITE_TRUNCATE_FILE;
        }

        // Box decreased but with less than 8 bytes and there is no following FreeSpaceBox:
        // Since there is no space to insert a FreeSpaceBox we have to:
        // Replace MovieBox at its original offset with FreeSpaceBox and
        // append MovieBox to the end of the file
        if ($sizeDiff > -8 && $nextBox instanceof FreeSpaceBox) {
            return self::SCENARIO_REPLACE_FREE_MOVE_TO_END;
        }

        // Box decreased and there is a following FreeSpaceBox:
        // Increase the size of the FreeSpaceBox by MovieBox's size diff
        return self::SCENARIO_OVERWRITE_RESIZE_FREE;
    }

    /**
     * Replace Movie Box's content
     *
     * @param string $content Must be same length as Movie Box's size
     */
    protected function replaceMovieBoxWithContent(MovieBox $movieBox, string $content): void
    {
        if (\strlen($content) !== $movieBox->getSize()) {
            throw new RuntimeException(sprintf('Content length (%d) differs from Movie Box size (%d) that should be replaced.', \strlen($content), $movieBox->getSize()));
        }
        echo 'Seek to '.$movieBox->getOffset()."\n";
        echo 'Length'.\strlen($content)."\n";
        $this->writeHandle->seek($movieBox->getOffset());
        $this->writeHandle->write($content);
    }

    /**
     * Truncate the file at given offset (=length)
     */
    protected function truncateFileAtOffset(int $offset): void
    {
        $this->writeHandle->truncate($offset);
    }

    /**
     * Replace Movie Box's content and adjust the adjacent Free Box's length
     */
    protected function replaceMovieBoxWithContentResizeFree(MovieBox $movieBox, string $content, FreeSpaceBox $freeSpaceBox, int $sizeDiff): void
    {
        $this->replaceMovieBoxWithContent($movieBox, $content);
        $freeSpaceBox->setSize($freeSpaceBox->getSize() + $sizeDiff);
        $freeSpaceBox->write();
    }

    /**
     * Replace Movie Box with Free Space Box at offset with identical size
     */
    protected function replaceMovieBoxWithFreeBox(MovieBox $movieBox): void
    {
        $freeBox = new FreeSpaceBox();
        $freeBox->constructDefault($movieBox->getSize());
        $freeBox->setOffset($movieBox->getOffset());
        $freeBox->setReadHandle($this->readHandle);
        $freeBox->setWriteHandle($this->writeHandle);

        $this->writeHandle->seek($movieBox->getOffset());
        $freeBox->write();
    }

    /**
     * Write (append) Movie Box content to the end of the file
     */
    protected function appendMovieBoxContentToFile(string $content): void
    {
        $this->writeHandle->seek($this->writeHandle->size());
        $this->writeHandle->write($content);
    }

    /**
     * Write Movie Box to memory and return its value
     */
    protected function newMovieBoxContent(MovieBox $movieBox): string
    {
        $memoryMoovHandle = MP4MemoryWriteHandle::memoryWritingFile();
        $movieBox->setWriteHandle($memoryMoovHandle);
        $movieBox->write();
        $memoryMoovHandle->rewind();

        $content = $memoryMoovHandle->getContents();

        if ($content !== false) {
            return $content;
        }

        throw new InvalidValueException('Handle did not contain a valid value.');
    }
}
