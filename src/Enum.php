<?php

/*
 * This file is part of Wikiwijs Maken.
 * Maintained by Kennisnet and published under the GNU licence.
 * See the LICENCE.md file for more information.
 */

namespace Kennisnet\Env;

// Thanks to all comments http://php.net/manual/en/class.splenum.php
abstract class Enum
{
    private static $constCacheArray = null;

    private function __construct()
    {
    }

    /**
     * @param      $name
     * @param bool $strict
     *
     * @return bool
     */
    public static function isValidName($name, $strict = false)
    {
        $constants = self::getValues();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));

        return in_array(strtolower($name), $keys);
    }

    /**
     * @return array
     */
    public static function getValues()
    {
        try {
            if (null == self::$constCacheArray) {
                self::$constCacheArray = [];
            }
            $calledClass = get_called_class();
            if (!array_key_exists($calledClass, self::$constCacheArray)) {
                $reflect                             = new \ReflectionClass($calledClass);
                self::$constCacheArray[$calledClass] = $reflect->getConstants();
            }

            return self::$constCacheArray[$calledClass];
        } catch (\ReflectionException $e) {
            return [];
        }
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isValidValue($value)
    {
        try {
            $values = array_values(self::getValues());

            return in_array($value, $values, $strict = true);
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
