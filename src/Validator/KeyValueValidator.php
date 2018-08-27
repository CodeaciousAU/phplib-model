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

            $type = gettype($val);
            $expectedType = $allowed[$key];
            if (($type == 'object' && !($val instanceof $expectedType))
                || ($expectedType == 'array' && !self::couldBeConventional($val))
                || ($expectedType == 'object' && !self::couldBeAssociative($val))
                || ($type != 'object' && $expectedType != 'object' && $type != $expectedType))
            {
                $errors[] = ValidationError::invalid('Incorrect type "'.self::displayType($type, $val)
                    .'", expecting "'.self::displayType($expectedType).'"', $key);
            }
        }
        return $errors;
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