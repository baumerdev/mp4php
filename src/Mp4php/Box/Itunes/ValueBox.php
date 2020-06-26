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

use Mp4php\Exceptions\ParserException;
use Mp4php\Exceptions\SizeException;

/**
 * Box for all children to MetadataBox (type 'ilst') parent not handled by GenericBox
 */
class ValueBox extends AbstractItunesBox
{
    /**
     * @var ValueBoxData[]
     */
    protected $data;

    protected $boxImmutable = false;

    protected $container = [MetadataBox::class];

    /**
     * @return ValueBoxData[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param ValueBoxData[] $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->setModified();
    }

    /**
     * Parse iTunes meta data
     *
     * @throws ParserException
     */
    protected function parse(): void
    {
        $end = $this->offset + $this->size;

        while ($this->readHandle->offset() < $end) {
            if (!\is_array($this->data)) {
                $this->data = [];
            }

            $data = $this->readHandle->readDataForType('data');
            $unpacked = unpack('Ntype/ncountry/nlanguage', $data);
            if ($unpacked) {
                $this->data[] = new ValueBoxData(
                    $unpacked['type'],
                    $unpacked['country'],
                    $unpacked['language'],
                    substr($data, 8)
                );
            }
        }

        if ($this->readHandle->offset() !== $end) {
            throw new SizeException(sprintf('Unexpected box end (expected %d, actual %d)', $end, $this->readHandle->offset()));
        }
    }

    /**
     * Write box's data
     */
    protected function writeModifiedContent(): void
    {
        if (\is_array($this->data)) {
            foreach ($this->data as $data) {
                $beginDataOffset = $this->writeTypeGetBoxOffset('data');

                // Type
                $this->writeHandle->write(pack('N', $data->type));
                // Country
                $this->writeHandle->write(pack('n', $data->country));
                // Language
                $this->writeHandle->write(pack('n', $data->language));
                // Value
                $this->writeHandle->write($data->value);

                $this->updateSizeAtOffset($beginDataOffset);
            }
        }
    }
}
