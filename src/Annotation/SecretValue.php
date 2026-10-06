<?php

namespace Kennisnet\Env\Annotation;

use Attribute;
use ReflectionProperty;

/**
 * Marks an AppEnv property as secret: its value is masked in the env check report instead of being
 * reported as a mismatch.
 *
 *     #[SecretValue]
 *     public $DATABASE_URL;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class SecretValue
{
    public static function isSetOn(string $class, string $property): bool
    {
        if (!property_exists($class, $property)) {
            return false;
        }

        return (new ReflectionProperty($class, $property))->getAttributes(self::class) !== [];
    }
}
