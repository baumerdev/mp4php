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

use DateTime;
use Mp4php\DataType\DataTypeInfoInterface;

/**
 * Trait for printing Box information
 */
trait BoxInfoTrait
{
    /**
     * @param bool $recursive
     * @param int  $level
     */
    public function info($recursive = true, $level = 0): void
    {
        $removeKeys = ['boxImmutable', 'parent', 'children', 'classesProperties', 'container', 'readHandle', 'writeHandle', 'type', 'offset', 'size', 'headerSize', 'modified'];
        $vars = \call_user_func('get_object_vars', $this);
        $displayVars = array_diff_key($vars, array_flip($removeKeys));
        //print_r($displayVars);

        $padding = str_repeat('    ', $level);
        $class = preg_replace('@.+\\\\([^\\\\]+?)(?:Box)$@', '$1', static::class);
        $type = $this->type ?? '';
        $offset = $this->offset ?? '';
        $size = $this->size ?? '';
        echo "\n".$padding.sprintf('=== %s (%s, %d:%d) ===', $class, $type, $offset, $size);
        if ($this->isModified()) {
            echo ' !';
        }
        echo "\n";

        if ($recursive) {
            $this->infoOutRecursive($displayVars, $level);

            $children = $this->getChildren() ?? '';
            if ($children) {
                echo $padding.'children ('.\count($children)."):\n";
                foreach ($children as $child) {
                    $child->info(true, $level + 1);
                }
            }
        }

        echo $padding.sprintf('/== %s (%s, %d:%d) ===', $class, $type, $offset, $size)."\n";
    }

    /**
     * @return Box[]|null
     */
    abstract protected function getChildren(): ?array;

    abstract protected function isModified(): bool;

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function infoOutRecursive(array $vars, int $level): void
    {
        $padding = str_repeat('    ', $level);

        foreach ($vars as $key => $value) {
            echo "$padding$key: ";
            if (\is_object($value) && is_a($value, Box::class)) {
                /** @var Box $value */
                echo "\n";
                $value->info(true, $level + 1);
            } elseif (\is_array($value)) {
                echo "\n";
                $this->infoOutRecursive($value, $level + 1);
            } elseif (\is_object($value) && is_a($value, DateTime::class)) {
                /** @var DateTime $value */
                echo $value->format('Y-m-d H:i:s');
            } elseif (\is_object($value) && is_a($value, DataTypeInfoInterface::class)) {
                echo "\n";
                /* @var DataTypeInfoInterface $value */
                $value->info($level + 1);
            } elseif (\is_string($value)) {
                echo $value;
            } elseif (\is_bool($value)) {
                echo '(bool)'.($value ? 'true' : 'false');
            } else {
                print_r($value);
            }
            echo "\n";
        }
    }
}
