<?php

namespace Kennisnet\Env\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionException;
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
     * @throws ReflectionException
     */
    public static function hasAnnotation($class, $property)
    {
        try {
            $annotationReader   = new AnnotationReader();
            $reflectionProperty = new ReflectionProperty($class, $property);
            $annotations        = array_filter(
                $annotationReader->getPropertyAnnotations($reflectionProperty),
                function ($annotation) {
                    return $annotation instanceof SecretValue;
                });

            return !empty($annotations);
        } catch (ReflectionException $reflectionException){
            return false;
        }
    }
}
