<?php

declare(strict_types=1);
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

namespace Mp4php\Box;

use Mp4php\Exceptions\ParserException;

/**
 * aligned(8) class TrackEncryptionBox extends FullBox(‘tenc’, version=0, flags=0)
 * {
 *   unsigned int(24)		default_IsEncrypted;
 *   unsigned int(8)		default_IV_size;
 *   unsigned int(8)[16]	default_KID;
 * }
 */
class TrackEncryptionBox extends AbstractFullBox
{
    const TYPE = 'tenc';

    protected $container = [SchemeInformationBox::class];

    /**
     * @var bool
     */
    protected $defaultIsEncrypted;

    /**
     * @var int
     */
    protected $defaultIVSize;

    /**
     * @var string
     */
    protected $defaultKID;

    public function isDefaultIsEncrypted(): bool
    {
        return $this->defaultIsEncrypted;
    }

    public function getDefaultIVSize(): int
    {
        return $this->defaultIVSize;
    }

    public function getDefaultKID(): string
    {
        return $this->defaultKID;
    }

    protected function parse(): void
    {
        parent::parse();

        $unpacked = unpack(
            'C3isEncrypted/CivSize',
            $this->readHandle->read(4)
        );
        if ($unpacked) {
            // Currently 0x000002 – 0xFFFFFF is reserved, so we can ignore the first two bytes
            $this->defaultIsEncrypted = $unpacked['isEncrypted3'] === 1;
            $this->defaultIVSize = $unpacked['ivSize'];
        } else {
            throw new ParserException('Cannot parse Track Encryption Box');
        }

        $this->defaultKID = $this->readHandle->read(16);
    }
}
