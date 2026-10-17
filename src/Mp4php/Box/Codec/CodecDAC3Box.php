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
use Override;

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
    public const string TYPE = 'dac3';

    public const int FLAG_SAMPLE_RATE_48 = 0;
    public const int FLAG_SAMPLE_RATE_44_1 = 1;
    public const int FLAG_SAMPLE_RATE_32 = 2;
    public const int FLAG_SAMPLE_RATE_RESERVED = 3;

    public const int FLAG_BITSTREAM_CM_COMPLETE = 0;
    public const int FLAG_BITSTREAM_ME_MUSIC_EFFECTS = 1;
    public const int FLAG_BITSTREAM_VI_VISUALLY_IMPAIRED = 2;
    public const int FLAG_BITSTREAM_HI_HEARING_IMPAIRED = 3;
    public const int FLAG_BITSTREAM_D_DIALOGUE = 4;
    public const int FLAG_BITSTREAM_C_COMMENTARY = 5;
    public const int FLAG_BITSTREAM_E_EMERGENCY = 6;
    public const int FLAG_BITSTREAM_VO_VOICE_OVER = 7;
    public const int FLAG_BITSTREAM_KARAOKE = 7;

    public const string BITSTREAM_CM_COMPLETE = 'cm_complete_main';
    public const string BITSTREAM_ME_MUSIC_EFFECTS = 'me_music_and_effects';
    public const string BITSTREAM_VI_VISUALLY_IMPAIRED = 'vi_visually_impaired';
    public const string BITSTREAM_HI_HEARING_IMPAIRED = 'hi_hearing_impaired';
    public const string BITSTREAM_D_DIALOGUE = 'd_dialogue';
    public const string BITSTREAM_C_COMMENTARY = 'c_commentary';
    public const string BITSTREAM_E_EMERGENCY = 'e_emergency';
    public const string BITSTREAM_VO_VOICE_OVER = 'vo_voice_over';
    public const string BITSTREAM_KARAOKE = 'karaoke';

    public const int FLAG_CHANNEL_1_PLUS_1 = 0;
    public const int FLAG_CHANNEL_MONO = 1;
    public const int FLAG_CHANNEL_STEREO = 2;
    public const int FLAG_CHANNEL_3F_0R = 3;
    public const int FLAG_CHANNEL_2F_1R = 4;
    public const int FLAG_CHANNEL_3F_1R = 5;
    public const int FLAG_CHANNEL_2F_2R = 6;
    public const int FLAG_CHANNEL_3F_2R = 7;

    public const string CHANNEL_1_PLUS_1 = '1+1';
    public const string CHANNEL_MONO = 'mono';
    public const string CHANNEL_STEREO = 'stereo';
    public const string CHANNEL_3F_0R = '3/0';
    public const string CHANNEL_2F_1R = '2/1';
    public const string CHANNEL_3F_1R = '3/1';
    public const string CHANNEL_2F_2R = '2/2';
    public const string CHANNEL_3F_2R = '3/2';

    public const int FLAG_BITRATE_32 = 0;
    public const int FLAG_BITRATE_40 = 1;
    public const int FLAG_BITRATE_48 = 2;
    public const int FLAG_BITRATE_56 = 3;
    public const int FLAG_BITRATE_64 = 4;
    public const int FLAG_BITRATE_80 = 5;
    public const int FLAG_BITRATE_96 = 6;
    public const int FLAG_BITRATE_112 = 7;
    public const int FLAG_BITRATE_128 = 8;
    public const int FLAG_BITRATE_160 = 9;
    public const int FLAG_BITRATE_192 = 10;
    public const int FLAG_BITRATE_224 = 11;
    public const int FLAG_BITRATE_256 = 12;
    public const int FLAG_BITRATE_320 = 13;
    public const int FLAG_BITRATE_384 = 14;
    public const int FLAG_BITRATE_448 = 15;
    public const int FLAG_BITRATE_512 = 16;
    public const int FLAG_BITRATE_576 = 17;
    public const int FLAG_BITRATE_640 = 18;

    /**
     * "fscode" Sampling Rate, kHz
     */
    protected ?float $sampleRate = null;
    /**
     * "bsid"
     */
    protected ?int $bitStreamId = null;
    /**
     * "bsmod"
     */
    protected ?string $bitStreamMode = null;
    /**
     * Channel layout
     *
     * @var string CHANNEL_*
     */
    protected ?string $channelLayout = null;
    /**
     * Subwoofer (LFE) on
     */
    protected ?bool $lfeOn = null;
    /**
     * Bitrate
     */
    protected ?int $bitrate = null;

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
    #[Override]
    protected function parse(): void
    {
        $flags = \sprintf('%024b', hexdec(bin2hex($this->readHandle->read(3))));
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
     */
    protected function parseSampleRate(int $valueSampleRate): void
    {
        $this->sampleRate = match ($valueSampleRate) {
            self::FLAG_SAMPLE_RATE_48 => 48,
            self::FLAG_SAMPLE_RATE_44_1 => 44.1,
            self::FLAG_SAMPLE_RATE_32 => 32,
            default => null,
        };
    }

    /**
     * Parse bit stream mode from binary string(s)
     */
    protected function parseBitStreamMode(int $valueBitStreamMode, int $valueChannel): void
    {
        $this->bitStreamMode = match ($valueBitStreamMode) {
            self::FLAG_BITSTREAM_CM_COMPLETE => self::BITSTREAM_CM_COMPLETE,
            self::FLAG_BITSTREAM_ME_MUSIC_EFFECTS => self::BITSTREAM_ME_MUSIC_EFFECTS,
            self::FLAG_BITSTREAM_VI_VISUALLY_IMPAIRED => self::BITSTREAM_VI_VISUALLY_IMPAIRED,
            self::FLAG_BITSTREAM_HI_HEARING_IMPAIRED => self::BITSTREAM_HI_HEARING_IMPAIRED,
            self::FLAG_BITSTREAM_D_DIALOGUE => self::BITSTREAM_D_DIALOGUE,
            self::FLAG_BITSTREAM_C_COMMENTARY => self::BITSTREAM_C_COMMENTARY,
            self::FLAG_BITSTREAM_E_EMERGENCY => self::BITSTREAM_E_EMERGENCY,
            // Same value as FLAG_BITSTREAM_KARAOKE, the channel decides
            self::FLAG_BITSTREAM_VO_VOICE_OVER => $valueChannel === self::FLAG_CHANNEL_MONO
                ? self::BITSTREAM_VO_VOICE_OVER
                : self::BITSTREAM_KARAOKE,
            default => null,
        };
    }

    /**
     * Parse channel layout from binary string
     */
    protected function parseChannels(int $valueChannel): void
    {
        $this->channelLayout = match ($valueChannel) {
            self::FLAG_CHANNEL_1_PLUS_1 => self::CHANNEL_1_PLUS_1,
            self::FLAG_CHANNEL_MONO => self::CHANNEL_MONO,
            self::FLAG_CHANNEL_STEREO => self::CHANNEL_STEREO,
            self::FLAG_CHANNEL_3F_0R => self::CHANNEL_3F_0R,
            self::FLAG_CHANNEL_2F_1R => self::CHANNEL_2F_1R,
            self::FLAG_CHANNEL_3F_1R => self::CHANNEL_3F_1R,
            self::FLAG_CHANNEL_2F_2R => self::CHANNEL_2F_2R,
            self::FLAG_CHANNEL_3F_2R => self::CHANNEL_3F_2R,
            default => null,
        };
    }

    /**
     * Parse bitrate from binary string
     */
    protected function parseBitrate(int $valueBitrate): void
    {
        $this->bitrate = match ($valueBitrate) {
            self::FLAG_BITRATE_32 => 32000,
            self::FLAG_BITRATE_40 => 40000,
            self::FLAG_BITRATE_48 => 48000,
            self::FLAG_BITRATE_56 => 56000,
            self::FLAG_BITRATE_64 => 64000,
            self::FLAG_BITRATE_80 => 80000,
            self::FLAG_BITRATE_96 => 96000,
            self::FLAG_BITRATE_112 => 112000,
            self::FLAG_BITRATE_128 => 128000,
            self::FLAG_BITRATE_160 => 160000,
            self::FLAG_BITRATE_192 => 192000,
            self::FLAG_BITRATE_224 => 224000,
            self::FLAG_BITRATE_256 => 256000,
            self::FLAG_BITRATE_320 => 320000,
            self::FLAG_BITRATE_384 => 384000,
            self::FLAG_BITRATE_448 => 448000,
            self::FLAG_BITRATE_512 => 512000,
            self::FLAG_BITRATE_576 => 576000,
            self::FLAG_BITRATE_640 => 640000,
            default => null,
        };
    }
}
