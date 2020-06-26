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

use Mp4php\Exceptions\NotInstantiableException;

/**
 * Class grouping quantity constants for properties
 */
final class PropertyQuantity
{
    /** Validation constant: Child if optional and can be unlimited */
    const ZERO_OR_MORE = '*';
    /** Validation constant: Child if optional but is limited to one */
    const ZERO_OR_ONE = '0-1';
    /** Validation constant: Child has to exist exactly one time */
    const ONE = '1';
    /** Validation constant: Child has to exist but can be unlimited */
    const ONE_OR_MORE = '1+';

    /**
     * Class must not be instantiated
     */
    public function __construct()
    {
        throw new NotInstantiableException();
    }
}
