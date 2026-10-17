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
    public const string ACKNOWLEDGEMENTS = '©cak';
    public const string ALBUM = '©alb';
    public const string ARTIST = '©ART';
    public const string ARTIST_ID = 'atID';
    public const string ALBUM_ARTIST = 'aART';
    public const string ART_DIRECTOR = '©art';
    public const string ARRANGER = '©arg';
    public const string CATEGORY = 'catg';
    public const string COMMENT = '©cmt';
    public const string COMPILATION = 'cpil';
    public const string COMPOSER = '©wrt';
    public const string COMPOSER_ID = 'cmID';
    public const string CONDUCTOR = '©con';
    public const string CONTENT_ID = 'cnID';
    public const string COPYRIGHT = 'cprt';
    public const string COVER = 'covr';
    public const string DESCRIPTION = 'desc';
    public const string DISK_NUMBER = 'disk';
    public const string ENCODED_BY = '©enc';
    public const string ENCODING_TOOL = '©too';
    public const string EPISODE_GLOBAL_ID = 'egid';
    public const string EXECUTIVE_PRODUCER = '©xpd';
    public const string GAPLESS_PLAYBACK = 'pgap';
    public const string GENRE = '©gen';
    public const string GENRE_ID = 'geID';
    public const string GENRE_TYPE = 'gnre';
    public const string GROUPING = '©grp';
    public const string HD_VIDEO = 'hdvd';
    public const string ITUNES_ACCOUNT = 'apID';
    public const string ITUNES_ACCOUNT_TYPE = 'akID';
    public const string ITUNES_COUNTRY = 'sfID';
    public const string KEYWORDS = 'keyw';
    public const string LINEAR_NOTES = '©lnt';
    public const string LONG_DESCRIPTION = 'ldes';
    public const string LYRICIST = '©aut';
    public const string LYRICS = '©lyr';
    public const string MEDIA_TYPE = 'stik';
    public const string MOVEMENT_NAME = '©mvn';
    public const string MOVEMENT_NUMBER = '©mvi';
    public const string MOVEMENT_COUNT = '©mvc';
    public const string NAME = '©nam';
    public const string ONLINE_EXTRAS = '©url';
    public const string ORIGINAL_ARTIST = '©ope';
    public const string OWNER = 'ownr';
    public const string PERFORMER = '©prf';
    public const string PHONOGRAM_RIGHTS = '©phg';
    public const string PLAYLIST_ID = 'plID';
    public const string PODCAST = 'pcst';
    public const string PODCAST_URL = 'purl';
    public const string PUBLISHER = '©pub';
    public const string PURCHASE_DATE = 'purd';
    public const string RATING = 'rtng';
    public const string RECORD_COMPANY = '©mak';
    public const string RELEASE_DATE = '©day';
    public const string SERIES_DESCRIPTION = 'sdes';
    public const string SHOW_WORK_MOVEMENT = 'shwm';
    public const string SOLOIST = '©sol';
    public const string SONG_DESCRIPTION = '©des';
    public const string SONG_PRODUCER = '©prd';
    public const string SORT_NAME = 'sonm';
    public const string SORT_ARTIST = 'soar';
    public const string SORT_ALBUM_ARTIST = 'soaa';
    public const string SORT_ALBUM = 'soal';
    public const string SORT_COMPOSER = 'soco';
    public const string SORT_TV_SHOW = 'sosn';
    public const string SOUND_ENGINEER = '©sne';
    public const string SOURCE = '©src';
    public const string TEMPO_BPM = 'tmpo';
    public const string THANKS = '©thx';
    public const string TV_NETWORK_NAME = 'tvnn';
    public const string TV_SHOW_NAME = 'tvsh';
    public const string TV_EPISODE_NUMBER = 'tven';
    public const string TV_SEASON = 'tvsn';
    public const string TV_EPISODE = 'tves';
    public const string TRACK_NUMBER = 'trkn';
    public const string TRACK_SUBTITLE = '©st3';
    public const string WORK_NAME = '©wrk';
    public const string XID = 'xid ';

    public const string STUDIO = '';
    public const string CAST = '';
    public const string DIRECTOR = '';
    public const string PRODUCER = '';
    public const string SCREENWRITER = '';

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
