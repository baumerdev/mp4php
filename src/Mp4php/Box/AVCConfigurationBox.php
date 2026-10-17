<?php
/*
 * MP4PHP
 * PHP library for parsing and modifying MP4 files
 *
 * Copyright © 2016-2021 Markus Baumer <markus@baumer.dev>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 * the GNU General Public License for more details.
 */

declare(strict_types=1);

namespace Mp4php\Box;

use Mp4php\Exceptions\ParserException;
use Override;

/**
 * class AVCConfigurationBox extends Box(‘avcC’) {
 *   AVCDecoderConfigurationRecord() AVCConfig;
 * }
 *
 * aligned(8) class AVCDecoderConfigurationRecord {
 *   unsigned int(8) configurationVersion = 1;
 *   unsigned int(8) AVCProfileIndication;
 *   unsigned int(8) profile_compatibility;
 *   unsigned int(8) AVCLevelIndication;
 *   bit(6) reserved = ‘111111’b;
 *   unsigned int(2) lengthSizeMinusOne;
 *   bit(3) reserved = ‘111’b;
 *   unsigned int(5) numOfSequenceParameterSets;
 *   for (i=0; i< numOfSequenceParameterSets; i++) {
 *     unsigned int(16) sequenceParameterSetLength ;
 *     bit(8*sequenceParameterSetLength) sequenceParameterSetNALUnit;
 *   }
 *   unsigned int(8) numOfPictureParameterSets;
 *   for (i=0; i< numOfPictureParameterSets; i++) {
 *     unsigned int(16) pictureParameterSetLength;
 *     bit(8*pictureParameterSetLength) pictureParameterSetNALUnit;
 *   }
 *   if( profile_idc == 100 || profile_idc == 110 ||
 *       profile_idc == 122 || profile_idc == 144 )
 *   {
 *     bit(6) reserved = ‘111111’b;
 *     unsigned int(2) chroma_format;
 *     bit(5) reserved = ‘11111’b;
 *     unsigned int(3) bit_depth_luma_minus8;
 *     bit(5) reserved = ‘11111’b;
 *     unsigned int(3) bit_depth_chroma_minus8;
 *     unsigned int(8) numOfSequenceParameterSetExt;
 *     for (i=0; i< numOfSequenceParameterSetExt; i++) {
 *       unsigned int(16) sequenceParameterSetExtLength;
 *       bit(8*sequenceParameterSetExtLength) sequenceParameterSetExtNALUnit;
 *     }
 *   }
 * }
 */
class AVCConfigurationBox extends Box
{
    public const string TYPE = 'avcC';

    public const int FLAG_PROFILE_INDICATION_NONE = 0;
    public const int FLAG_PROFILE_INDICATION_CALV = 44;
    public const int FLAG_PROFILE_INDICATION_BASELINE = 66;
    public const int FLAG_PROFILE_INDICATION_MAIN = 77;
    public const int FLAG_PROFILE_INDICATION_SCALABLE_BASELINE = 83;
    public const int FLAG_PROFILE_INDICATION_EXTENDED = 88;
    public const int FLAG_PROFILE_INDICATION_SCALABLE_HIGH = 89;
    public const int FLAG_PROFILE_INDICATION_HIGH = 100;
    public const int FLAG_PROFILE_INDICATION_HIGH10 = 110;
    public const int FLAG_PROFILE_INDICATION_MULTIVIEW_HIGH = 118;
    public const int FLAG_PROFILE_INDICATION_HIGH422 = 122;
    public const int FLAG_PROFILE_INDICATION_STEREO_HIGH = 128;
    public const int FLAG_PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH = 138;
    public const int FLAG_PROFILE_INDICATION_HIGH444_REMOVED = 144;
    public const int FLAG_PROFILE_INDICATION_HIGH444 = 244;

    public const string PROFILE_INDICATION_NONE = 'None';
    public const string PROFILE_INDICATION_CALV = 'CAVLC 4:4:4';
    public const string PROFILE_INDICATION_BASELINE = 'Baseline';
    public const string PROFILE_INDICATION_MAIN = 'Main';
    public const string PROFILE_INDICATION_SCALABLE_BASELINE = 'Scalable Baseline';
    public const string PROFILE_INDICATION_EXTENDED = 'Extended';
    public const string PROFILE_INDICATION_SCALABLE_HIGH = 'Scalable High';
    public const string PROFILE_INDICATION_HIGH = 'High';
    public const string PROFILE_INDICATION_HIGH10 = 'High 10';
    public const string PROFILE_INDICATION_MULTIVIEW_HIGH = 'Multiview High';
    public const string PROFILE_INDICATION_HIGH422 = 'High 4:2:2';
    public const string PROFILE_INDICATION_STEREO_HIGH = 'Stereo High';
    public const string PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH = 'Multiview Depth High';
    public const string PROFILE_INDICATION_HIGH444_REMOVED = 'High 4:4.4 (removed)';
    public const string PROFILE_INDICATION_HIGH444 = 'High 4:4.4 Predictive';

    public const int FLAG_CHROMA_YUV_420 = 1;
    public const int FLAG_CHROMA_YUV_422 = 2;
    public const int FLAG_CHROMA_YUV_444 = 3;

    public const string CHROMA_YUV_420 = 'YUV 4:2:0';
    public const string CHROMA_YUV_422 = 'YUV 4:2:2';
    public const string CHROMA_YUV_444 = 'YUV 4:4:4';

    protected array $container = [VisualSampleEntryBox::class];

    protected ?int $configurationVersion = null;

    protected ?string $profile = null;

    protected ?int $profileCompatibility = null;

    protected ?string $level = null;

    protected ?int $nalUnitLength = null;

    protected ?array $sequenceParamSets = null;

    protected ?array $pictureParamSets = null;

    protected ?string $chromaFormat = null;

    protected ?int $bitDepthLuma = null;

    protected ?int $bitDepthChroma = null;

    protected ?array $sequenceParamSetsExt = null;

    public function getConfigurationVersion(): int
    {
        return $this->configurationVersion;
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    public function getProfileCompatibility(): int
    {
        return $this->profileCompatibility;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getNalUnitLength(): int
    {
        return $this->nalUnitLength;
    }

    public function getSequenceParamSets(): array
    {
        return $this->sequenceParamSets;
    }

    public function getPictureParamSets(): array
    {
        return $this->pictureParamSets;
    }

    public function getChromaFormat(): ?string
    {
        return $this->chromaFormat;
    }

    public function getBitDepthLuma(): ?int
    {
        return $this->bitDepthLuma;
    }

    public function getBitDepthChroma(): ?int
    {
        return $this->bitDepthChroma;
    }

    public function getSequenceParamSetsExt(): ?array
    {
        return $this->sequenceParamSetsExt;
    }

    #[Override]
    protected function parse(): void
    {
        $unpacked = unpack(
            'Cversion/Cprofile/Ccompatibility/Clevel/CnaluSize',
            $this->readHandle->read(5)
        );

        if (!$unpacked) {
            throw new ParserException('Cannot parse AVC Configuration Box');
        }
        $this->configurationVersion = $unpacked['version'];
        $this->profile = $this->profileIndication($unpacked['profile']);
        $this->profileCompatibility = $unpacked['compatibility'];
        $this->level = \sprintf('%.1f', $unpacked['level'] / 10);
        $this->nalUnitLength = $unpacked['naluSize'] - 251;

        $this->sequenceParamSets = $this->parseParameterSets(224);
        $this->pictureParamSets = $this->parseParameterSets();

        $this->parseExtended();
    }

    protected function parseExtended(): void
    {
        if (!\in_array($this->profile, [
            self::PROFILE_INDICATION_HIGH,
            self::PROFILE_INDICATION_HIGH10,
            self::PROFILE_INDICATION_HIGH422,
            self::PROFILE_INDICATION_HIGH444_REMOVED,
        ])) {
            return;
        }

        // Many encoders omit the extension even for these profiles
        if ($this->remainingBytes() === null) {
            return;
        }

        $unpacked = unpack(
            'Cchroma/CdepthLuma/CdepthChroma',
            $this->readHandle->read(3)
        );

        if (!$unpacked) {
            throw new ParserException('Cannot parse AVC Configuration Box');
        }

        $this->chromaFormat = $this->chromaFormat($unpacked['chroma'] - 252);
        $this->bitDepthLuma = $unpacked['depthLuma'] - 240;
        $this->bitDepthChroma = $unpacked['depthChroma'] - 240;

        $this->sequenceParamSetsExt = $this->parseParameterSets();
    }

    protected function parseParameterSets(int $countOffset = 0): array
    {
        $unpacked = unpack(
            'Ccount',
            $this->readHandle->read(1)
        );
        if (!$unpacked) {
            throw new ParserException('Cannot parse AVC Configuration Box');
        }

        $numOfParameterSets = $unpacked['count'] - $countOffset;

        $parameterSets = [];
        for ($i = 0; $i < $numOfParameterSets; ++$i) {
            $unpacked = unpack(
                'nlength',
                $this->readHandle->read(2)
            );
            if (!$unpacked) {
                throw new ParserException('Cannot parse AVC Configuration Box');
            }
            $parameterSets[] = $this->readHandle->read($unpacked['length']);
        }

        return $parameterSets;
    }

    protected function profileIndication(int $profile): string
    {
        return match ($profile) {
            self::FLAG_PROFILE_INDICATION_NONE => self::PROFILE_INDICATION_NONE,
            self::FLAG_PROFILE_INDICATION_CALV => self::PROFILE_INDICATION_CALV,
            self::FLAG_PROFILE_INDICATION_BASELINE => self::PROFILE_INDICATION_BASELINE,
            self::FLAG_PROFILE_INDICATION_MAIN => self::PROFILE_INDICATION_MAIN,
            self::FLAG_PROFILE_INDICATION_SCALABLE_BASELINE => self::PROFILE_INDICATION_SCALABLE_BASELINE,
            self::FLAG_PROFILE_INDICATION_EXTENDED => self::PROFILE_INDICATION_EXTENDED,
            self::FLAG_PROFILE_INDICATION_SCALABLE_HIGH => self::PROFILE_INDICATION_SCALABLE_HIGH,
            self::FLAG_PROFILE_INDICATION_HIGH => self::PROFILE_INDICATION_HIGH,
            self::FLAG_PROFILE_INDICATION_HIGH10 => self::PROFILE_INDICATION_HIGH10,
            self::FLAG_PROFILE_INDICATION_MULTIVIEW_HIGH => self::PROFILE_INDICATION_MULTIVIEW_HIGH,
            self::FLAG_PROFILE_INDICATION_HIGH422 => self::PROFILE_INDICATION_HIGH422,
            self::FLAG_PROFILE_INDICATION_STEREO_HIGH => self::PROFILE_INDICATION_STEREO_HIGH,
            self::FLAG_PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH => self::PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH,
            self::FLAG_PROFILE_INDICATION_HIGH444_REMOVED => self::PROFILE_INDICATION_HIGH444_REMOVED,
            self::FLAG_PROFILE_INDICATION_HIGH444 => self::PROFILE_INDICATION_HIGH444,
            default => throw new ParserException('Unknown profile indication'),
        };
    }

    protected function chromaFormat(int $chroma): string
    {
        return match ($chroma) {
            // Chroma format 0 should be handled like chroma format 1
            0, self::FLAG_CHROMA_YUV_420 => self::CHROMA_YUV_420,
            self::FLAG_CHROMA_YUV_422 => self::CHROMA_YUV_422,
            self::FLAG_CHROMA_YUV_444 => self::CHROMA_YUV_444,
            default => throw new ParserException('Unknown chroma format'),
        };
    }
}
