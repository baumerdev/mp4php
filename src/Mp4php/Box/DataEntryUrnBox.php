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

namespace Mp4php\Box;

use Mp4php\Exceptions\SizeException;

/**
 * Data Entry URL Box (type 'urn ')
 *
 * aligned(8) class DataEntryUrnBox (bit(24) flags)
 *     extends FullBox(‘urn ’, version = 0, flags) {
 *     string name;
 *     string location;
 * }
 */
class DataEntryUrnBox extends AbstractFullBox
{
    const TYPE = 'urn ';

    const FLAG_SELF_CONTAINMENT = 1;

    protected $container = [DataReferenceBox::class];

    /**
     * @var string|null
     */
    protected $urn;
    /**
     * @var string|null
     */
    protected $location;

    public function getUrn(): ?string
    {
        return $this->urn;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Parse the Data Entry URL Box
     */
    protected function parse(): void
    {
        // AbstractFullBox
        parent::parse();

        if (!($this->flags & self::FLAG_SELF_CONTAINMENT)) {
            $remainingBytes = $this->remainingBytes();
            if ($remainingBytes === null || $remainingBytes < 1) {
                throw new SizeException('No bytes left for name/location value.');
            }
            $entryData = explode("\x00", $this->readHandle->read($remainingBytes));
            $this->urn = $entryData[0];
            if (isset($entryData[1])) {
                $this->location = $entryData[1];
            }
        }
    }
}
