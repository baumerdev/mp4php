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

use LogicException;
use Mp4php\DataType\PropertyQuantity;
use Mp4php\Exceptions\ParserException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Validate parent and child Boxes
 */
class BoxValidation
{
    /**
     * @var Box
     */
    protected $box;

    /**
     * BoxValidation constructor.
     */
    public function __construct(Box $box)
    {
        $this->box = $box;
    }

    /**
     * BoxValidation static constructor.
     *
     * @return BoxValidation
     */
    public static function withBox(Box $box)
    {
        return new self($box);
    }

    /**
     * Check if this box is allowed to be a child parent
     *
     * @param string[]|false[] $container
     */
    public function validateParentClass(?Box $parent, array $container): void
    {
        if ($parent === null || \count($container) < 1) {
            return;
        }

        foreach ($container as $class) {
            if (!\is_string($class) || is_a($parent, $class)) {
                return;
            }
        }

        throw new ParserException(sprintf('"%s" is not allowed to be a child of "%s"', \get_class($this->box), \get_class($parent)));
    }

    /**
     * Validate that all properties are filled as defined in its quantities
     *
     * @param array $classesProperties [className => [propertyName, PropertyQuantity::*]]
     */
    public function validateClassProperties(array $classesProperties): void
    {
        $getterMethods = $this->getterMethods();
        $thisClass = \get_class($this->box);

        foreach ($classesProperties as $class => $settings) {
            $property = $settings[0];
            $quantity = $settings[1];

            $getterMethodName = $getterMethods[$property];
            if (!$getterMethodName) {
                throw new LogicException(sprintf('No getter method found for property %s.', $property));
            }

            if ($quantity === PropertyQuantity::ONE &&
                (!\is_object($this->box->{$getterMethodName}()) || !is_a($this->box->{$getterMethodName}(), $class))) {
                throw new ParserException(sprintf('"%s" for property "%s" in "%s" is not set.', $class, $property, $thisClass));
            }
            if ($quantity === PropertyQuantity::ONE_OR_MORE &&
                (!\is_array($this->box->{$getterMethodName}()) || \count($this->box->{$getterMethodName}()) < 1)) {
                throw new ParserException(sprintf('"%s"es for property "%s" in "%s" are not set.', $class, $property, $thisClass));
            }
        }
    }

    /**
     * Get all getter methods for classes properties
     *
     * @return array [property => method]
     */
    protected function getterMethods()
    {
        try {
            $reflect = new ReflectionClass($this->box);
        } catch (ReflectionException $ex) {
            return [];
        }

        $getterMethods = [];
        $properties = $reflect->getProperties();
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($properties as $property) {
            foreach ($methods as $method) {
                if (\in_array(
                    strtolower($method->getName()),
                    ['get'.strtolower($property->getName()), strtolower('is'.$property->getName())]
                )) {
                    $getterMethods[$property->getName()] = $method->getName();
                }
            }
        }

        return $getterMethods;
    }
}
