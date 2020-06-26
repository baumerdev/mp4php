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

namespace tests\Mp4php\File;

use Mp4php\File\MP4ReadHandle;

class MockMP4ReadHandle extends MP4ReadHandle
{
    public function setContent(string $content): void
    {
        $this->seek(0);
        fwrite($this->handle(), $content);
        $this->seek(0);
    }

    public function setHexContent(string $hexContent): void
    {
        $this->setContent((string) hex2bin($hexContent));
    }
}
