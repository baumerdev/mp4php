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
 * aligned(8) class SchemeTypeBox extends FullBox('schm', 0, flags) {
 *   unsigned int(32) scheme_type; // 4CC identifying the scheme
 *   unsigned int(32) scheme_version; // scheme version
 *   if (flags & 0x000001) {
 *     unsigned int(8) scheme_uri[]; // browser uri
 *   }
 * }
 */
class SchemeTypeBox extends AbstractFullBox
{
    const TYPE = 'schm';

    const FLAG_HAS_SCHEME_URI = 1;

    protected $container = [ProtectionSchemeInfoBox::class];

    /**
     * @var string
     */
    protected $schemeType;
    /**
     * @var string
     */
    protected $schemeVersion;
    /**
     * @var string|null
     */
    protected $schemeUri;

    public function getSchemeType(): string
    {
        return $this->schemeType;
    }

    public function getSchemeVersion(): string
    {
        return $this->schemeVersion;
    }

    public function getSchemeUri(): ?string
    {
        return $this->schemeUri;
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $this->schemeType = $this->readHandle->read(4);

        if ($unpacked = unpack('nmajor/nminor', $this->readHandle->read(4))) {
            $this->schemeVersion = sprintf('%d.%d', $unpacked['major'], $unpacked['version']);
        } else {
            throw new ParserException('Cannot parse version.');
        }

        if ($this->checkFlag(self::FLAG_HAS_SCHEME_URI)) {
            $read = $this->readHandle->read($this->remainingBytes());
            if (\strlen($read) < 1) {
                throw new ParserException('Scheme URI missing');
            }
            $this->schemeUri = substr($read, 0, -1);
        }
    }
}
