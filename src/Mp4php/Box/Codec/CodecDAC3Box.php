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

namespace Mp4php\Box\Codec;

use Mp4php\Box\Box;

/**
 * Box for AC3 codec details (type 'dac3')
 *
 * Class AC3SpecificBox
 * {
 *   unsigned int(2) fscod;
 *   unsigned int(5) bsid;
 *   unsigned int(3) bsmod;
 *   unsigned int(3) acmod;
 *   unsigned int(1) lfeon;
 *   unsigned int(5) bit_rate_code;
 *   unsigned int(5) reserved = 0;
 * }
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CodecDAC3Box extends Box
{
    const TYPE = 'dac3';

    const FLAG_SAMPLE_RATE_48 = 0;
    const FLAG_SAMPLE_RATE_44_1 = 1;
    const FLAG_SAMPLE_RATE_32 = 2;
    const FLAG_SAMPLE_RATE_RESERVED = 3;

    const FLAG_BITSTREAM_CM_COMPLETE = 0;
    const FLAG_BITSTREAM_ME_MUSIC_EFFECTS = 1;
    const FLAG_BITSTREAM_VI_VISUALLY_IMPAIRED = 2;
    const FLAG_BITSTREAM_HI_HEARING_IMPAIRED = 3;
    const FLAG_BITSTREAM_D_DIALOGUE = 4;
    const FLAG_BITSTREAM_C_COMMENTARY = 5;
    const FLAG_BITSTREAM_E_EMERGENCY = 6;
    const FLAG_BITSTREAM_VO_VOICE_OVER = 7;
    const FLAG_BITSTREAM_KARAOKE = 7;

    const BITSTREAM_CM_COMPLETE = 'cm_complete_main';
    const BITSTREAM_ME_MUSIC_EFFECTS = 'me_music_and_effects';
    const BITSTREAM_VI_VISUALLY_IMPAIRED = 'vi_visually_impaired';
    const BITSTREAM_HI_HEARING_IMPAIRED = 'hi_hearing_impaired';
    const BITSTREAM_D_DIALOGUE = 'd_dialogue';
    const BITSTREAM_C_COMMENTARY = 'c_commentary';
    const BITSTREAM_E_EMERGENCY = 'e_emergency';
    const BITSTREAM_VO_VOICE_OVER = 'vo_voice_over';
    const BITSTREAM_KARAOKE = 'karaoke';

    const FLAG_CHANNEL_1_PLUS_1 = 0;
    const FLAG_CHANNEL_MONO = 1;
    const FLAG_CHANNEL_STEREO = 2;
    const FLAG_CHANNEL_3F_0R = 3;
    const FLAG_CHANNEL_2F_1R = 4;
    const FLAG_CHANNEL_3F_1R = 5;
    const FLAG_CHANNEL_2F_2R = 6;
    const FLAG_CHANNEL_3F_2R = 7;

    const CHANNEL_1_PLUS_1 = '1+1';
    const CHANNEL_MONO = 'mono';
    const CHANNEL_STEREO = 'stereo';
    const CHANNEL_3F_0R = '3/0';
    const CHANNEL_2F_1R = '2/1';
    const CHANNEL_3F_1R = '3/1';
    const CHANNEL_2F_2R = '2/2';
    const CHANNEL_3F_2R = '3/2';

    const FLAG_BITRATE_32 = 0;
    const FLAG_BITRATE_40 = 1;
    const FLAG_BITRATE_48 = 2;
    const FLAG_BITRATE_56 = 3;
    const FLAG_BITRATE_64 = 4;
    const FLAG_BITRATE_80 = 5;
    const FLAG_BITRATE_96 = 6;
    const FLAG_BITRATE_112 = 7;
    const FLAG_BITRATE_128 = 8;
    const FLAG_BITRATE_160 = 9;
    const FLAG_BITRATE_192 = 10;
    const FLAG_BITRATE_224 = 11;
    const FLAG_BITRATE_256 = 12;
    const FLAG_BITRATE_320 = 13;
    const FLAG_BITRATE_384 = 14;
    const FLAG_BITRATE_448 = 15;
    const FLAG_BITRATE_512 = 16;
    const FLAG_BITRATE_576 = 17;
    const FLAG_BITRATE_640 = 18;

    /**
     * "fscode" Sampling Rate, kHz
     *
     * @var float|null
     */
    protected $sampleRate;
    /**
     * "bsid"
     *
     * @var int
     */
    protected $bitStreamId;
    /**
     * "bsmod"
     *
     * @var string
     */
    protected $bitStreamMode;
    /**
     * Channel layout
     *
     * @var string CHANNEL_*
     */
    protected $channelLayout;
    /**
     * Subwoofer (LFE) on
     *
     * @var bool
     */
    protected $lfeOn;
    /**
     * Bitrate
     *
     * @var int
     */
    protected $bitrate;

    public function getSampleRate(): ?float
    {
        return $this->sampleRate;
    }

    public function getBitStreamId(): int
    {
        return $this->bitStreamId;
    }

    public function getBitStreamMode(): string
    {
        return $this->bitStreamMode;
    }

    public function getChannelLayout(): string
    {
        return $this->channelLayout;
    }

    public function isLfeOn(): bool
    {
        return $this->lfeOn;
    }

    public function getBitrate(): int
    {
        return $this->bitrate;
    }

    /**
     * Parse box's channel and subwoofer info
     */
    protected function parse(): void
    {
        $flags = sprintf('%024b', hexdec(bin2hex($this->readHandle->read(3))));
        $valueSampleRate = bindec(substr($flags, 0, 2));
        $this->bitStreamId = bindec(substr($flags, 2, 5));
        $valueBitStreamMode = bindec(substr($flags, 7, 3));
        $valueChannel = bindec(substr($flags, 10, 3));
        $valueSubwoofer = bindec(substr($flags, 13, 1));
        $valueBitrate = bindec(substr($flags, 14, 5));

        $this->lfeOn = $valueSubwoofer > 0;

        $this->parseSampleRate($valueSampleRate);
        $this->parseBitStreamMode($valueBitStreamMode, $valueChannel);
        $this->parseChannels($valueChannel);
        $this->parseBitrate($valueBitrate);
    }

    /**
     * Parse sample rate from binary string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function parseSampleRate(int $valueSampleRate): void
    {
        switch ($valueSampleRate) {
            case self::FLAG_SAMPLE_RATE_48:
                $this->sampleRate = 48;
                break;
            case self::FLAG_SAMPLE_RATE_44_1:
                $this->sampleRate = 44.1;
                break;
            case self::FLAG_SAMPLE_RATE_32:
                $this->sampleRate = 32;
                break;
            default:
                $this->sampleRate = null;
                break;
        }
    }

    /**
     * Parse bit stream mode from binary string(s)
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function parseBitStreamMode(int $valueBitStreamMode, int $valueChannel): void
    {
        switch ($valueBitStreamMode) {
            case self::FLAG_BITSTREAM_CM_COMPLETE:
                $this->bitStreamMode = self::BITSTREAM_CM_COMPLETE;
                break;
            case self::FLAG_BITSTREAM_ME_MUSIC_EFFECTS:
                $this->bitStreamMode = self::BITSTREAM_ME_MUSIC_EFFECTS;
                break;
            case self::FLAG_BITSTREAM_VI_VISUALLY_IMPAIRED:
                $this->bitStreamMode = self::BITSTREAM_VI_VISUALLY_IMPAIRED;
                break;
            case self::FLAG_BITSTREAM_HI_HEARING_IMPAIRED:
                $this->bitStreamMode = self::BITSTREAM_HI_HEARING_IMPAIRED;
                break;
            case self::FLAG_BITSTREAM_D_DIALOGUE:
                $this->bitStreamMode = self::BITSTREAM_D_DIALOGUE;
                break;
            case self::FLAG_BITSTREAM_C_COMMENTARY:
                $this->bitStreamMode = self::BITSTREAM_C_COMMENTARY;
                break;
            case self::FLAG_BITSTREAM_E_EMERGENCY:
                $this->bitStreamMode = self::BITSTREAM_E_EMERGENCY;
                break;
            case self::FLAG_BITSTREAM_VO_VOICE_OVER:
            case self::FLAG_BITSTREAM_KARAOKE:
                if ($valueChannel === self::FLAG_CHANNEL_MONO) {
                    $this->bitStreamMode = self::BITSTREAM_VO_VOICE_OVER;
                } else {
                    $this->bitStreamMode = self::BITSTREAM_KARAOKE;
                }
                break;
        }
    }

    /**
     * Parse channel layout from binary string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function parseChannels(int $valueChannel): void
    {
        switch ($valueChannel) {
            case self::FLAG_CHANNEL_1_PLUS_1:
                $this->channelLayout = self::CHANNEL_1_PLUS_1;
                break;
            case self::FLAG_CHANNEL_MONO:
                $this->channelLayout = self::CHANNEL_MONO;
                break;
            case self::FLAG_CHANNEL_STEREO:
                $this->channelLayout = self::CHANNEL_STEREO;
                break;
            case self::FLAG_CHANNEL_3F_0R:
                $this->channelLayout = self::CHANNEL_3F_0R;
                break;
            case self::FLAG_CHANNEL_2F_1R:
                $this->channelLayout = self::CHANNEL_2F_1R;
                break;
            case self::FLAG_CHANNEL_3F_1R:
                $this->channelLayout = self::CHANNEL_3F_1R;
                break;
            case self::FLAG_CHANNEL_2F_2R:
                $this->channelLayout = self::CHANNEL_2F_2R;
                break;
            case self::FLAG_CHANNEL_3F_2R:
                $this->channelLayout = self::CHANNEL_3F_2R;
                break;
        }
    }

    /**
     * Parse bitrate from binary string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function parseBitrate(int $valueBitrate): void
    {
        switch ($valueBitrate) {
            case self::FLAG_BITRATE_32:
                $this->bitrate = 32000;
                break;
            case self::FLAG_BITRATE_40:
                $this->bitrate = 40000;
                break;
            case self::FLAG_BITRATE_48:
                $this->bitrate = 48000;
                break;
            case self::FLAG_BITRATE_56:
                $this->bitrate = 56000;
                break;
            case self::FLAG_BITRATE_64:
                $this->bitrate = 64000;
                break;
            case self::FLAG_BITRATE_80:
                $this->bitrate = 80000;
                break;
            case self::FLAG_BITRATE_96:
                $this->bitrate = 96000;
                break;
            case self::FLAG_BITRATE_112:
                $this->bitrate = 112000;
                break;
            case self::FLAG_BITRATE_128:
                $this->bitrate = 128000;
                break;
            case self::FLAG_BITRATE_160:
                $this->bitrate = 160000;
                break;
            case self::FLAG_BITRATE_192:
                $this->bitrate = 192000;
                break;
            case self::FLAG_BITRATE_224:
                $this->bitrate = 224000;
                break;
            case self::FLAG_BITRATE_256:
                $this->bitrate = 256000;
                break;
            case self::FLAG_BITRATE_320:
                $this->bitrate = 320000;
                break;
            case self::FLAG_BITRATE_384:
                $this->bitrate = 384000;
                break;
            case self::FLAG_BITRATE_448:
                $this->bitrate = 448000;
                break;
            case self::FLAG_BITRATE_512:
                $this->bitrate = 512000;
                break;
            case self::FLAG_BITRATE_576:
                $this->bitrate = 576000;
                break;
            case self::FLAG_BITRATE_640:
                $this->bitrate = 640000;
                break;
        }
    }
}
