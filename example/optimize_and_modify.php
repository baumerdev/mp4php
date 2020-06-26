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

/*******************************************************************
 * This example does the same as optimize.php but with some
 * modifications within the moov box.
 *******************************************************************/

declare(strict_types=1);
use Mp4php\Mp4php;
use Mp4php\Optimize;

require __DIR__.'/../bootstrap.php';

$inputFile = __DIR__.'/assets/big_buck_bunny_5s.mp4';
$outputFile = __DIR__.'/assets/big_buck_bunny_5s.optimized-modified.mp4';

$mp4php = new Mp4php($inputFile);
$mp4php->parse();

// FFMPEG sometimes sets filetype headers that can cause
// incompatibility with E-AC3 audio tracks on some Apple devices
$mp4php->changeISOMtoM4VHeader();

// FFMPEG sometimes adds "Serif" font information.
// Remove it, since you usually want sans-serif subtitles
$mp4php->movTextFormatReset();

// Sets audio and subtitle tracks metadata and fix alternate
// groups and track references.
$trackId = 0;
$audioTracks = [
    (++$trackId) => [
        'language' => 'eng',
        'name' => 'English Stereo',
        'default' => true,
    ],
];
$subtitleTracks = [
    (++$trackId) => [
        'language' => 'eng',
        'name' => 'English Subtitles',
        'forced' => true,
    ],
    (++$trackId) => [
        'language' => 'deu',
        'name' => 'Deutscher Untertitel',
        'forced' => false,
    ],
];
$mp4php->fixAlternateGroupsAndTracks($audioTracks, $subtitleTracks);

// Set the Name
$mp4php->setName('Big Buck Bunny (Short Movie)');

$mp4php->optimize($outputFile, Optimize::FREE_SPACE_DISABLE);
