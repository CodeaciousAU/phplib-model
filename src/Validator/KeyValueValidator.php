<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\Model\Validator;

use Codeacious\Model\ValidationError;

class KeyValueValidator
{
    /**
     * Validate that an input array doesn't contain any unexpected keys, and that its values are the
     * correct type.
     *
     * Type names that can be specified are:
     * - boolean
     * - integer
     * - double
     * - string
     * - array: Ensures the value is a numerically-indexed array
     * - object: Ensures the value is an associative array
     * - a specific class name: Ensures the value is an object of the given class or a subclass
     *
     * Null values always validate successfully.
     *
     * @param array $input Associative array of data to validate
     * @param array $allowed Associative array where the keys define the keys that are permitted in
     *    the input, and the values define the data types that are expected
     * @return ValidationError[]
     */
    public static function getErrors(array $input, array $allowed)
    {
        $errors = [];
        foreach ($input as $key => $val)
        {
            if (!array_key_exists($key, $allowed))
            {
                $errors[] = ValidationError::unrecognized('Unrecognized property', $key);
                continue;
            }

            //Null is always allowed
            if ($val === null)
                continue;

            $expectedType = $allowed[$key];
            if (!self::isExpectedType($val, $expectedType))
            {
                $errors[] = ValidationError::invalid('Incorrect type "'
                    .self::displayType(gettype($val), $val).'", expecting "'
                    .self::displayType($expectedType).'"', $key);
            }
        }
        return $errors;
    }

    /**
     * @param mixed $val
     * @param string $expectedType
     * @return bool
     */
    private static function isExpectedType($val, $expectedType)
    {
        $type = gettype($val);
        if ($type == 'object')
            return ($val instanceof $expectedType);
        if ($expectedType == 'array')
            return self::couldBeConventional($val);
        if ($expectedType == 'object')
            return self::couldBeAssociative($val);
        if ($expectedType == 'double')
            return (is_int($val) || is_float($val));
        return ($type == $expectedType);
    }

    /**
     * @param string $type
     * @param mixed $val
     * @return string
     */
    private static function displayType($type, $val=null)
    {
        if ($type == 'double')
            return 'float';

        if ($type == 'array' && $val !== null && self::couldBeAssociative($val))
            return 'object';

        $parts = explode('\\', $type);
        return array_pop($parts);
    }

    /**
     * @param mixed $array
     * @return bool
     */
    private static function couldBeAssociative($array)
    {
        if (!is_array($array))
            return false;

        $keys = array_keys($array);
        if (empty($keys))
            return true;

        foreach ($keys as $key)
        {
            if (!is_int($key))
                return true;
        }
        return false;
    }
    /**
     * @param mixed $array
     * @return bool
     */
    private static function couldBeConventional($array)
    {
        if (!is_array($array))
            return false;

        $keys = array_keys($array);
        if (empty($keys))
            return true;

        return !self::couldBeAssociative($array);
    }
}