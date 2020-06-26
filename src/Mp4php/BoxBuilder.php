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

use Mp4php\Box\Codec;
use Mp4php\Box\Itunes;
use Mp4php\Exceptions\UnsupportedFormatException;
use Mp4php\File\MP4ReadHandle;

/**
 * Builder for ISO Boxes
 */
class BoxBuilder
{
    /**
     * Classes corresponding to the 4-char box type
     *
     * @var array
     */
    protected static $boxClasses = [
        Box\TrackReferenceChapterList::TYPE => Box\TrackReferenceChapterList::class,
        Box\ChunkLargeOffsetBox::TYPE => Box\ChunkLargeOffsetBox::class,
        Box\CompositionTimeToSampleBox::TYPE => Box\CompositionTimeToSampleBox::class,
        Box\DataInformationBox::TYPE => Box\DataInformationBox::class,
        Box\DataReferenceBox::TYPE => Box\DataReferenceBox::class,
        Box\EditBox::TYPE => Box\EditBox::class,
        Box\EditListBox::TYPE => Box\EditListBox::class,
        Box\ElementaryStreamDescriptorBox::TYPE => Box\ElementaryStreamDescriptorBox::class,
        Box\TrackReferenceFollowSubtitle::TYPE => Box\TrackReferenceFollowSubtitle::class,
        Box\FontTableBox::TYPE => Box\FontTableBox::class,
        Box\FileTypeBox::TYPE => Box\FileTypeBox::class,
        Box\HandlerReferenceBox::TYPE => Box\HandlerReferenceBox::class,
        Box\HintMediaHeaderBox::TYPE => Box\HintMediaHeaderBox::class,
        Box\ObjectDescriptorBox::TYPE => Box\ObjectDescriptorBox::class,
        Itunes\MetadataBox::TYPE => Itunes\MetadataBox::class,
        Box\MediaDataBox::TYPE => Box\MediaDataBox::class,
        Box\MovieExtendsHeaderBox::TYPE => Box\MovieExtendsHeaderBox::class,
        Box\MetaBox::TYPE => Box\MetaBox::class,
        Box\MediaBox::TYPE => Box\MediaBox::class,
        Box\MediaHeaderBox::TYPE => Box\MediaHeaderBox::class,
        Box\MovieFragmentHeaderBox::TYPE => Box\MovieFragmentHeaderBox::class,
        Box\MediaInformationBox::TYPE => Box\MediaInformationBox::class,
        Box\MovieFragmentBox::TYPE => Box\MovieFragmentBox::class,
        Box\MovieBox::TYPE => Box\MovieBox::class,
        Box\MovieExtendsBox::TYPE => Box\MovieExtendsBox::class,
        Box\MovieHeaderBox::TYPE => Box\MovieHeaderBox::class,
        Box\NameBox::TYPE => Box\NameBox::class,
        Box\NullMediaHeaderBox::TYPE => Box\NullMediaHeaderBox::class,
        Box\PixelAspectRatioBox::TYPE => Box\PixelAspectRatioBox::class,
        Box\SoundMediaHeaderBox::TYPE => Box\SoundMediaHeaderBox::class,
        Box\SampleTableBox::TYPE => Box\SampleTableBox::class,
        Box\SampleGroupDescriptionBox::TYPE => Box\SampleGroupDescriptionBox::class,
        Box\SampleToGroupBox::TYPE => Box\SampleToGroupBox::class,
        Box\ChunkOffsetBox::TYPE => Box\ChunkOffsetBox::class,
        Box\SampleToChunkBox::TYPE => Box\SampleToChunkBox::class,
        Box\SampleDescriptionBox::TYPE => Box\SampleDescriptionBox::class,
        Box\SampleSizeBox::TYPE => Box\SampleSizeBox::class,
        Box\SubtitleSampleEntryBox::TYPE => Box\SubtitleSampleEntryBox::class,
        Box\SyncSampleBox::TYPE => Box\SyncSampleBox::class,
        Box\DecodingTimeToSampleBox::TYPE => Box\DecodingTimeToSampleBox::class,
        Box\TrackFragmentHeaderBox::TYPE => Box\TrackFragmentHeaderBox::class,
        Box\TitleBox::TYPE => Box\TitleBox::class,
        Box\TrackFragmentBox::TYPE => Box\TrackFragmentBox::class,
        Box\TrackBox::TYPE => Box\TrackBox::class,
        Box\TrackReferenceBox::TYPE => Box\TrackReferenceBox::class,
        Box\TrackExtensionProperty::TYPE => Box\TrackExtensionProperty::class,
        Box\TrackExtendsBox::TYPE => Box\TrackExtendsBox::class,
        Box\TrackFragmentRunBox::TYPE => Box\TrackFragmentRunBox::class,
        Box\TrackHeaderBox::TYPE => Box\TrackHeaderBox::class,
        Box\UserDataBox::TYPE => Box\UserDataBox::class,
        Box\DataEntryUrlBox::TYPE => Box\DataEntryUrlBox::class,
        Box\VideoMediaHeaderBox::TYPE => Box\VideoMediaHeaderBox::class,

        // Codec
        Codec\CodecDAC3Box::TYPE => Codec\CodecDAC3Box::class,
        Codec\CodecDEC3Box::TYPE => Codec\CodecDEC3Box::class,

        // iTunes Generic
        '----' => Itunes\GenericBox::class,

        // Unused space/data to be deleted
        Box\FreeSpaceBox::TYPE => Box\FreeSpaceBox::class,
        Box\FreeSpaceBox::TYPE_SKIP => Box\FreeSpaceBox::class,
        Box\FreeSpaceBox::TYPE_WIDE => Box\FreeSpaceBox::class,
    ];

    /**
     * Return class name for box type
     *
     * @param string $type 4-char string
     */
    public static function classForType(string $type): ?string
    {
        if (isset(self::$boxClasses[$type])) {
            return self::$boxClasses[$type];
        }

        return null;
    }

    /**
     * Parse ISO MP4 content and return boxes
     */
    public static function parseBoxes(MP4ReadHandle $readHandle): array
    {
        $boxes = [];
        while ($box = self::parsedBox($readHandle)) {
            // Check for format
            if (\count($boxes) < 1) {
                // We have to get "ftyp" box as first box in file
                if (!is_a($box, Box\FileTypeBox::class)) {
                    throw new UnsupportedFormatException('Expected box "ftyp" not found.');
                }
                // Check for supported major brand
                if (!\in_array($box->getMajorBrand(), ['isom', 'iso2', 'mp41', 'mp42', 'M4A ', 'M4V '])) {
                    throw new UnsupportedFormatException(sprintf('Unsupported major brand in "ftyp": %s', $box->getMajorBrand()));
                }
            }
            $boxes[] = $box;
        }

        return $boxes;
    }

    /**
     * Reads from handle and returns appropriate parsed Box
     *
     * @param Box\Box|null $parent parent element
     */
    public static function parsedBox(MP4ReadHandle $readHandle, $parent = null): ?Box\Box
    {
        // Skip if we are at the end
        if ($readHandle->isEndOfFile()) {
            return null;
        }

        [$type, $size, $largeSize] = $readHandle->readSizeType();

        return self::buildBoxAndParse($type, $readHandle, $size, $largeSize, $parent);
    }

    /**
     * Get the appropriate box for name
     *
     * @param int|null     $largeSize 64bit size if $size == 1
     * @param Box\Box|null $parent    Parent element
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function buildBoxAndParse(string $type, MP4ReadHandle $readHandle, ?int $size = null, ?int $largeSize = null, ?Box\Box $parent = null): Box\Box
    {
        if ($boxClass = self::classForType($type)) {
            if ($boxClass === Itunes\GenericBox::class) {
                $box = new Itunes\GenericBox($parent, $type);
            } else {
                /** @var Box\Box $box */
                $box = new $boxClass($parent);
            }
        } elseif ($parent !== null && is_subclass_of($parent, Itunes\AbstractItunesBox::class)) {
            $box = new Itunes\ValueBox($parent, $type);
        } elseif ($parent !== null && is_a($parent, Box\TrackReferenceBox::class)) {
            $box = new Box\TrackReferenceTypeBox($parent, $type);
        } elseif ($parent !== null &&
            is_a($parent, Box\MediaInformationBox::class) &&
            $parent->containingBoxesCount() < 1
        ) {
            $box = new Box\UnknownMediaHeaderBox($parent, $type);
        } else {
            $box = new Box\Box($parent, $type);
        }

        return $box->constructParse($readHandle, $size, $largeSize, $parent);
    }
}
