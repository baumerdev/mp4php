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

use Mp4php\Exceptions\InvalidValueException;
use Mp4php\Exceptions\ParserException;

/**
 * Handler Reference Box (type 'hdlr')
 */
class HandlerReferenceBox extends AbstractFullBox
{
    const TYPE = 'hdlr';

    const VIDEO_TRACK = 'vide';
    const AUDIO_TRACK = 'soun';
    const HINT_TRACK = 'hint';
    const METADATA_TRACK = 'meta';
    const AUXILIARY_TRACK = 'auxv';
    const APPLE_METADATA = 'mdir';
    const SUBTITLE = 'sbtl';
    const TEXT = 'text';

    protected $boxImmutable = false;

    protected $container = [MediaBox::class, MetaBox::class];

    /**
     * @var int
     */
    protected $preDefined;
    /**
     * @var string One of the class's constants
     */
    protected $handlerType;
    /**
     * This should be zero but Apple writes 'appl' here
     *
     * @var string|null
     */
    protected $reserved1;
    /**
     * @var string
     */
    protected $name;

    public function getHandlerType(): string
    {
        return $this->handlerType;
    }

    public function setHandlerType(string $handlerType): void
    {
        if ($this->handlerType === $handlerType) {
            return;
        }

        $this->handlerType = $handlerType;
        $this->setModified();
    }

    public function getReserved1(): ?string
    {
        return $this->reserved1;
    }

    public function setReserved1(?string $reserved1): void
    {
        if ($this->reserved1 === $reserved1) {
            return;
        }

        if ($reserved1 !== null && $reserved1 !== 'appl') {
            throw new InvalidValueException(sprintf('Reserved1 can only be NULL or "appl" but "%s" given.', $reserved1));
        }

        $this->reserved1 = $reserved1;
        $this->setModified();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($this->name === $name) {
            return;
        }

        $this->name = $name;
        $this->setModified();
    }

    /**
     * Parse the box's data
     */
    protected function parse(): void
    {
        parent::parse();

        $unpackFormat = 'NpreDefined/H8handlerType/H8reserved1/Nreserved2/Nreserved3';
        if ($unpacked = unpack($unpackFormat, $this->readHandle->read(20))) {
            $this->preDefined = $unpacked['preDefined'];
            $this->handlerType = (string) hex2bin($unpacked['handlerType']);
            $this->reserved1 = $unpacked['reserved1'] !== '00000000' ? (string) hex2bin($unpacked['reserved1']) : null;
        } else {
            throw new ParserException('Cannot parse Handler Reference Box');
        }

        $remainingSize = $this->remainingBytes();
        if ($remainingSize > 0) {
            $name = substr($this->readHandle->read($remainingSize), 0, -1);
            if (\strlen($name) > 0) {
                $this->name = $name;
            }
        } else {
            throw new ParserException('Cannot parse Handler Reference Box name');
        }
    }

    /**
     * Write box's content
     */
    protected function writeModifiedContent(): void
    {
        // Predefined
        $this->writeHandle->write(pack('N', $this->preDefined));
        // HandlerType
        $this->writeHandle->write($this->handlerType);
        // Reserved1
        if ($this->reserved1) {
            $this->writeHandle->write($this->reserved1);
        } else {
            $this->writeHandle->write(pack('x4'));
        }
        // Reserved2 + Reserved3
        $this->writeHandle->write(pack('x9'));
    }
}
