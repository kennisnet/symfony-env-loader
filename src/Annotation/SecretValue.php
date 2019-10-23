<?php

/*
 * This file is part of Wikiwijs Maken.
 * Maintained by Kennisnet and published under the GNU licence.
 * See the LICENCE.md file for more information.
 */

namespace Kennisnet\Env\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionProperty;

/**
 * Class SecretValue.
 *
 * @Annotation
 */
class SecretValue extends Annotation
{
    /**
     * @param $class
     * @param $property
     *
     * @return bool
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function hasAnnotation($class, $property)
    {
        $annotationReader   = new AnnotationReader();
        $reflectionProperty = new ReflectionProperty($class, $property);
        $annotations        = array_filter(
            $annotationReader->getPropertyAnnotations($reflectionProperty),
            function ($annotation) {
                return $annotation instanceof SecretValue;
            });

        return !empty($annotations);
    }
}
