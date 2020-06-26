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

use Mp4php\DataType\FixedPoint;
use Mp4php\DataType\PropertyQuantity;
use Mp4php\Exceptions\ParserException;

/**
 * Audio Sample Entry Box (stsd entry for handlerType soun)
 *
 * class AudioSampleEntry(codingname) extends SampleEntry (codingname){
 *     const unsigned int(32)[2] reserved = 0;
 *     template unsigned int(16) channelcount = 2;
 *     template unsigned int(16) samplesize = 16;
 *     unsigned int(16) pre_defined = 0;
 *     const unsigned int(16) reserved = 0 ;
 *     template unsigned int(32) samplerate = { default samplerate of media}<<16;
 * }
 */
class AudioSampleEntryBox extends AbstractSampleEntryBox
{
    protected $classesProperties = [
        ElementaryStreamDescriptorBox::class => ['esDescriptor', PropertyQuantity::ZERO_OR_ONE],
    ];

    /**
     * @var int
     */
    protected $channelCount;
    /**
     * @var int
     */
    protected $sampleSize;
    /**
     * @var FixedPoint 16.16
     */
    protected $sampleRate;
    /**
     * @var ElementaryStreamDescriptorBox|null
     */
    protected $esDescriptor;

    public function getChannelCount(): int
    {
        return $this->channelCount;
    }

    public function getSampleSize(): int
    {
        return $this->sampleSize;
    }

    public function getSampleRate(): FixedPoint
    {
        return $this->sampleRate;
    }

    public function getEsDescriptor(): ?ElementaryStreamDescriptorBox
    {
        return $this->esDescriptor;
    }

    /**
     * Parse the Audio Sample Entry Box
     */
    protected function parse(): void
    {
        // AbstractSampleEntryBox
        parent::parse();

        $unpacked = unpack(
            'N2reserved/nchannelCount/nsampleSize/npreDefined/nreserved/NsampleRate',
            $this->readHandle->read(20)
        );
        if ($unpacked) {
            $this->channelCount = $unpacked['channelCount'];
            $this->sampleSize = $unpacked['sampleSize'];
            $this->sampleRate = FixedPoint::createFromInt($unpacked['sampleRate'], 16, 16);
        } else {
            throw new ParserException('Cannot parse Audio Sample Entry Box');
        }

        $this->parseChildren();
    }
}
