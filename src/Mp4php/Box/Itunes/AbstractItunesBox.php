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

namespace Mp4php\Box\Itunes;

use Mp4php\Box\Box;
use ReflectionClass;

/**
 * Parent class to all iTunes boxes in moov.udta.meta.ilst
 */
abstract class AbstractItunesBox extends Box
{
    public const ACKNOWLEDGEMENTS = '©cak';
    public const ALBUM = '©alb';
    public const ARTIST = '©ART';
    public const ARTIST_ID = 'atID';
    public const ALBUM_ARTIST = 'aART';
    public const ART_DIRECTOR = '©art';
    public const ARRANGER = '©arg';
    public const CATEGORY = 'catg';
    public const COMMENT = '©cmt';
    public const COMPILATION = 'cpil';
    public const COMPOSER = '©wrt';
    public const COMPOSER_ID = 'cmID';
    public const CONDUCTOR = '©con';
    public const CONTENT_ID = 'cnID';
    public const COPYRIGHT = 'cprt';
    public const COVER = 'covr';
    public const DESCRIPTION = 'desc';
    public const DISK_NUMBER = 'disk';
    public const ENCODED_BY = '©enc';
    public const ENCODING_TOOL = '©too';
    public const EPISODE_GLOBAL_ID = 'egid';
    public const EXECUTIVE_PRODUCER = '©xpd';
    public const GAPLESS_PLAYBACK = 'pgap';
    public const GENRE = '©gen';
    public const GENRE_ID = 'geID';
    public const GENRE_TYPE = 'gnre';
    public const GROUPING = '©grp';
    public const HD_VIDEO = 'hdvd';
    public const ITUNES_ACCOUNT = 'apID';
    public const ITUNES_ACCOUNT_TYPE = 'akID';
    public const ITUNES_COUNTRY = 'sfID';
    public const KEYWORDS = 'keyw';
    public const LINEAR_NOTES = '©lnt';
    public const LONG_DESCRIPTION = 'ldes';
    public const LYRICIST = '©aut';
    public const LYRICS = '©lyr';
    public const MEDIA_TYPE = 'stik';
    public const MOVEMENT_NAME = '©mvn';
    public const MOVEMENT_NUMBER = '©mvi';
    public const MOVEMENT_COUNT = '©mvc';
    public const NAME = '©nam';
    public const ONLINE_EXTRAS = '©url';
    public const ORIGINAL_ARTIST = '©ope';
    public const OWNER = 'ownr';
    public const PERFORMER = '©prf';
    public const PHONOGRAM_RIGHTS = '©phg';
    public const PLAYLIST_ID = 'plID';
    public const PODCAST = 'pcst';
    public const PODCAST_URL = 'purl';
    public const PUBLISHER = '©pub';
    public const PURCHASE_DATE = 'purd';
    public const RATING = 'rtng';
    public const RECORD_COMPANY = '©mak';
    public const RELEASE_DATE = '©day';
    public const SERIES_DESCRIPTION = 'sdes';
    public const SHOW_WORK_MOVEMENT = 'shwm';
    public const SOLOIST = '©sol';
    public const SONG_DESCRIPTION = '©des';
    public const SONG_PRODUCER = '©prd';
    public const SORT_NAME = 'sonm';
    public const SORT_ARTIST = 'soar';
    public const SORT_ALBUM_ARTIST = 'soaa';
    public const SORT_ALBUM = 'soal';
    public const SORT_COMPOSER = 'soco';
    public const SORT_TV_SHOW = 'sosn';
    public const SOUND_ENGINEER = '©sne';
    public const SOURCE = '©src';
    public const TEMPO_BPM = 'tmpo';
    public const THANKS = '©thx';
    public const TV_NETWORK_NAME = 'tvnn';
    public const TV_SHOW_NAME = 'tvsh';
    public const TV_EPISODE_NUMBER = 'tven';
    public const TV_SEASON = 'tvsn';
    public const TV_EPISODE = 'tves';
    public const TRACK_NUMBER = 'trkn';
    public const TRACK_SUBTITLE = '©st3';
    public const WORK_NAME = '©wrk';
    public const XID = 'xid ';

    public const STUDIO = '';
    public const CAST = '';
    public const DIRECTOR = '';
    public const PRODUCER = '';
    public const SCREENWRITER = '';

    /**
     * Get all constants
     *
     * @return string[]
     */
    public static function getTypes(): array
    {
        $reflection = new ReflectionClass(self::class);

        return $reflection->getConstants();
    }

    /**
     * Check of type is known
     */
    protected function isTypeKnown(): bool
    {
        return \in_array($this->type, self::getTypes());
    }
}
