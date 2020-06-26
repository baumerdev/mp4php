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
    const ACKNOWLEDGEMENTS = '©cak';
    const ALBUM = '©alb';
    const ARTIST = '©ART';
    const ARTIST_ID = 'atID';
    const ALBUM_ARTIST = 'aART';
    const ART_DIRECTOR = '©art';
    const ARRANGER = '©arg';
    const CATEGORY = 'catg';
    const COMMENT = '©cmt';
    const COMPILATION = 'cpil';
    const COMPOSER = '©wrt';
    const COMPOSER_ID = 'cmID';
    const CONDUCTOR = '©con';
    const CONTENT_ID = 'cnID';
    const COPYRIGHT = 'cprt';
    const COVER = 'covr';
    const DESCRIPTION = 'desc';
    const DISK_NUMBER = 'disk';
    const ENCODED_BY = '©enc';
    const ENCODING_TOOL = '©too';
    const EPISODE_GLOBAL_ID = 'egid';
    const EXECUTIVE_PRODUCER = '©xpd';
    const GAPLESS_PLAYBACK = 'pgap';
    const GENRE = '©gen';
    const GENRE_ID = 'geID';
    const GENRE_TYPE = 'gnre';
    const GROUPING = '©grp';
    const HD_VIDEO = 'hdvd';
    const ITUNES_ACCOUNT = 'apID';
    const ITUNES_ACCOUNT_TYPE = 'akID';
    const ITUNES_COUNTRY = 'sfID';
    const KEYWORDS = 'keyw';
    const LINEAR_NOTES = '©lnt';
    const LONG_DESCRIPTION = 'ldes';
    const LYRICIST = '©aut';
    const LYRICS = '©lyr';
    const MEDIA_TYPE = 'stik';
    const MOVEMENT_NAME = '©mvn';
    const MOVEMENT_NUMBER = '©mvi';
    const MOVEMENT_COUNT = '©mvc';
    const NAME = '©nam';
    const ONLINE_EXTRAS = '©url';
    const ORIGINAL_ARTIST = '©ope';
    const OWNER = 'ownr';
    const PERFORMER = '©prf';
    const PHONOGRAM_RIGHTS = '©phg';
    const PLAYLIST_ID = 'plID';
    const PODCAST = 'pcst';
    const PODCAST_URL = 'purl';
    const PUBLISHER = '©pub';
    const PURCHASE_DATE = 'purd';
    const RATING = 'rtng';
    const RECORD_COMPANY = '©mak';
    const RELEASE_DATE = '©day';
    const SERIES_DESCRIPTION = 'sdes';
    const SHOW_WORK_MOVEMENT = 'shwm';
    const SOLOIST = '©sol';
    const SONG_DESCRIPTION = '©des';
    const SONG_PRODUCER = '©prd';
    const SORT_NAME = 'sonm';
    const SORT_ARTIST = 'soar';
    const SORT_ALBUM_ARTIST = 'soaa';
    const SORT_ALBUM = 'soal';
    const SORT_COMPOSER = 'soco';
    const SORT_TV_SHOW = 'sosn';
    const SOUND_ENGINEER = '©sne';
    const SOURCE = '©src';
    const TEMPO_BPM = 'tmpo';
    const THANKS = '©thx';
    const TV_NETWORK_NAME = 'tvnn';
    const TV_SHOW_NAME = 'tvsh';
    const TV_EPISODE_NUMBER = 'tven';
    const TV_SEASON = 'tvsn';
    const TV_EPISODE = 'tves';
    const TRACK_NUMBER = 'trkn';
    const TRACK_SUBTITLE = '©st3';
    const WORK_NAME = '©wrk';
    const XID = 'xid ';

    const STUDIO = '';
    const CAST = '';
    const DIRECTOR = '';
    const PRODUCER = '';
    const SCREENWRITER = '';

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
