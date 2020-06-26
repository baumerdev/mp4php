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
 * This example takes a MP4/M4V file as input, parses its structure
 * saves it as a new file
 *******************************************************************/

declare(strict_types=1);
use Mp4php\Mp4php;

require __DIR__.'/../bootstrap.php';

$inputFile = __DIR__.'/assets/big_buck_bunny_5s.mp4';
$outputFile = __DIR__.'/assets/big_buck_bunny_5s.saved.mp4';

$mp4php = new Mp4php($inputFile);
$mp4php->parse();
$mp4php->save($outputFile);
