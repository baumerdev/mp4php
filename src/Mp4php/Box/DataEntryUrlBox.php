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
 * Data Entry URL Box (type 'url ')
 *
 * aligned(8) class DataEntryUrlBox (bit(24) flags)
 *     extends FullBox(‘url ’, version = 0, flags) {
 *     string location;
 * }
 */
class DataEntryUrlBox extends AbstractFullBox
{
    const TYPE = 'url ';

    const FLAG_SELF_CONTAINMENT = 1;

    protected $container = [DataReferenceBox::class];

    /**
     * @var string|null
     */
    protected $location;

    /**
     * @return string
     */
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
                throw new SizeException('No bytes left for location value.');
            }
            $this->location = $this->readHandle->read($remainingBytes);
        }
    }
}
