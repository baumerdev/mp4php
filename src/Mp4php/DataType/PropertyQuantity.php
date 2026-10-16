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

namespace Mp4php\DataType;

/**
 * Quantity of child boxes for properties
 */
enum PropertyQuantity
{
    /**
     * Validation constant: Child if optional and can be unlimited
     */
    case ZERO_OR_MORE;
    /**
     * Validation constant: Child if optional but is limited to one
     */
    case ZERO_OR_ONE;
    /**
     * Validation constant: Child has to exist exactly one time
     */
    case ONE;
    /**
     * Validation constant: Child has to exist but can be unlimited
     */
    case ONE_OR_MORE;

    public function label(): string
    {
        return match ($this) {
            self::ZERO_OR_MORE => 'optional, any number',
            self::ZERO_OR_ONE => 'optional, at most one',
            self::ONE => 'exactly one',
            self::ONE_OR_MORE => 'at least one',
        };
    }
}
