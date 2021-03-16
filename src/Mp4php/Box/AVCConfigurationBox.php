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
    const TYPE = 'avcC';

    const FLAG_PROFILE_INDICATION_NONE = 0;
    const FLAG_PROFILE_INDICATION_CALV = 44;
    const FLAG_PROFILE_INDICATION_BASELINE = 66;
    const FLAG_PROFILE_INDICATION_MAIN = 77;
    const FLAG_PROFILE_INDICATION_SCALABLE_BASELINE = 83;
    const FLAG_PROFILE_INDICATION_EXTENDED = 88;
    const FLAG_PROFILE_INDICATION_SCALABLE_HIGH = 89;
    const FLAG_PROFILE_INDICATION_HIGH = 100;
    const FLAG_PROFILE_INDICATION_HIGH10 = 110;
    const FLAG_PROFILE_INDICATION_MULTIVIEW_HIGH = 118;
    const FLAG_PROFILE_INDICATION_HIGH422 = 122;
    const FLAG_PROFILE_INDICATION_STEREO_HIGH = 128;
    const FLAG_PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH = 138;
    const FLAG_PROFILE_INDICATION_HIGH444_REMOVED = 144;
    const FLAG_PROFILE_INDICATION_HIGH444 = 244;

    const PROFILE_INDICATION_NONE = 'None';
    const PROFILE_INDICATION_CALV = 'CAVLC 4:4:4';
    const PROFILE_INDICATION_BASELINE = 'Baseline';
    const PROFILE_INDICATION_MAIN = 'Main';
    const PROFILE_INDICATION_SCALABLE_BASELINE = 'Scalable Baseline';
    const PROFILE_INDICATION_EXTENDED = 'Extended';
    const PROFILE_INDICATION_SCALABLE_HIGH = 'Scalable High';
    const PROFILE_INDICATION_HIGH = 'High';
    const PROFILE_INDICATION_HIGH10 = 'High 10';
    const PROFILE_INDICATION_MULTIVIEW_HIGH = 'Multiview High';
    const PROFILE_INDICATION_HIGH422 = 'High 4:2:2';
    const PROFILE_INDICATION_STEREO_HIGH = 'Stereo High';
    const PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH = 'Multiview Depth High';
    const PROFILE_INDICATION_HIGH444_REMOVED = 'High 4:4.4 (removed)';
    const PROFILE_INDICATION_HIGH444 = 'High 4:4.4 Predictive';

    protected $container = [VisualSampleEntryBox::class];

    /**
     * @var int
     */
    protected $configurationVersion;

    /**
     * @var string
     */
    protected $profileIndication;

    /**
     * @var int
     */
    protected $profileCompatibility;

    /**
     * @var string
     */
    protected $levelIndication;

    /**
     * @var int
     */
    protected $nalUnitLength;

    /**
     * @var array
     */
    protected $sequenceParamSets;

    /**
     * @var array
     */
    protected $pictureParamSets;

    /**
     * @var int|null
     */
    protected $chromaFormat;

    /**
     * @var int|null
     */
    protected $bitDepthLuma;

    /**
     * @var int|null
     */
    protected $bitDepthChroma;

    /**
     * @var array|null
     */
    protected $sequenceParamSetsExt;

    public function getConfigurationVersion(): int
    {
        return $this->configurationVersion;
    }

    public function getProfileIndication(): string
    {
        return $this->profileIndication;
    }

    public function getProfileCompatibility(): int
    {
        return $this->profileCompatibility;
    }

    public function getLevelIndication(): string
    {
        return $this->levelIndication;
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

    public function getChromaFormat(): ?int
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
        $this->profileIndication = $this->profileIndicationString($unpacked['profile']);
        $this->profileCompatibility = $unpacked['compatibility'];
        $this->levelIndication = sprintf('%.1f', $unpacked['level'] / 10);
        $this->nalUnitLength = $unpacked['naluSize'] - 251;

        $this->sequenceParamSets = $this->parseParameterSets(224);
        $this->pictureParamSets = $this->parseParameterSets();

        $this->parseExtended();
    }

    protected function parseExtended(): void
    {
        if (!\in_array($this->profileIndication, [
            self::PROFILE_INDICATION_HIGH,
            self::PROFILE_INDICATION_HIGH10,
            self::PROFILE_INDICATION_HIGH422,
            self::PROFILE_INDICATION_HIGH444_REMOVED,
            ])) {
            return;
        }

        $unpacked = unpack(
            'Cchroma/CdepthLuma/CdepthChroma',
            $this->readHandle->read(3)
        );

        if (!$unpacked) {
            throw new ParserException('Cannot parse AVC Configuration Box');
        }

        $this->chromaFormat = $unpacked['chroma'] - 252;
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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function profileIndicationString(int $profile): string
    {
        switch ($profile) {
            case self::FLAG_PROFILE_INDICATION_NONE:
                return self::PROFILE_INDICATION_NONE;
            case self::FLAG_PROFILE_INDICATION_CALV:
                return self::PROFILE_INDICATION_CALV;
            case self::FLAG_PROFILE_INDICATION_BASELINE:
                return self::PROFILE_INDICATION_BASELINE;
            case self::FLAG_PROFILE_INDICATION_MAIN:
                return self::PROFILE_INDICATION_MAIN;
            case self::FLAG_PROFILE_INDICATION_SCALABLE_BASELINE:
                return self::PROFILE_INDICATION_SCALABLE_BASELINE;
            case self::FLAG_PROFILE_INDICATION_EXTENDED:
                return self::PROFILE_INDICATION_EXTENDED;
            case self::FLAG_PROFILE_INDICATION_SCALABLE_HIGH:
                return self::PROFILE_INDICATION_SCALABLE_HIGH;
            case self::FLAG_PROFILE_INDICATION_HIGH:
                return self::PROFILE_INDICATION_HIGH;
            case self::FLAG_PROFILE_INDICATION_HIGH10:
                return self::PROFILE_INDICATION_HIGH10;
            case self::FLAG_PROFILE_INDICATION_MULTIVIEW_HIGH:
                return self::PROFILE_INDICATION_MULTIVIEW_HIGH;
            case self::FLAG_PROFILE_INDICATION_HIGH422:
                return self::PROFILE_INDICATION_HIGH422;
            case self::FLAG_PROFILE_INDICATION_STEREO_HIGH:
                return self::PROFILE_INDICATION_STEREO_HIGH;
            case self::FLAG_PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH:
                return self::PROFILE_INDICATION_MULTIVIEW_DEPTH_HIGH;
            case self::FLAG_PROFILE_INDICATION_HIGH444_REMOVED:
                return self::PROFILE_INDICATION_HIGH444_REMOVED;
            case self::FLAG_PROFILE_INDICATION_HIGH444:
                return self::PROFILE_INDICATION_HIGH444;
            default:
                throw new ParserException('Unknown profile indication');
        }
    }
}
