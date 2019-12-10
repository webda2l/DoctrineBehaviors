<?php

declare(strict_types=1);

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Reflection;

class ClassAnalyzer
{
    /**
     * Return TRUE if the given object use the given trait, FALSE if not
     * @param ReflectionClass $class
     */
    public function hasTrait(\ReflectionClass $class, string $traitName, bool $isRecursive = false)
    {
        if (in_array($traitName, $class->getTraitNames(), true)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (($isRecursive === false) || ($parentClass === false) || ($parentClass === null)) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName, $isRecursive);
    }

    /**
     * Return TRUE if the given object has the given method, FALSE if not
     * @param ReflectionClass $class
     */
    public function hasMethod(\ReflectionClass $class, string $methodName)
    {
        return $class->hasMethod($methodName);
    }

    /**
     * Return TRUE if the given object has the given property, FALSE if not
     * @param ReflectionClass $class
     */
    public function hasProperty(\ReflectionClass $class, string $propertyName)
    {
        if ($class->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if ($parentClass === false) {
            return false;
        }

        return $this->hasProperty($parentClass, $propertyName);
    }
}
