<?php
namespace Agl\Core\Data;

/**
 * As an alternative to the scalar type hinting hack, this class provide methods
 * to validate functions/methods parameters.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Validation
{
    /**
     * Validate the parameters contained in the associative array $pParams.
     * Throw a new Exception if a data type is not supported.
     *
     * @param array $pParams Associative array (type => value)
     * @return bool
     */
    public static function check(array $pParams)
    {
        foreach ($pParams as $type => $value) {
            $func = 'is' . $type;

            if (method_exists(__CLASS__, $func)) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        if (self::$func($subValue) === false) {
                            return false;
                        }
                    }
                } else if (self::$func($value) === false) {
                    return false;
                }
            } else {
                if (! self::isRegex($type, $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate a boolean.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isBool($pValue)
    {
        return is_bool($pValue);
    }

    /**
     * Validate an integer.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isInt($pValue)
    {
        return is_int($pValue);
    }

    /**
     * Validate a double (float).
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isDouble($pValue)
    {
        return is_float($pValue);
    }

    /**
     * Validate a string.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isString($pValue)
    {
        return is_string($pValue);
    }

    /**
     * Check if a value is not empty.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isNotEmpty($pValue)
    {
        return (! empty($pValue));
    }

    /**
     * Validate a NULL value.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isNull($pValue)
    {
        return ($pValue === NULL);
    }

    /**
     * Validate a digit string.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isDigit($pValue)
    {
        return ctype_digit($pValue);
    }

    /**
     * Validate a numeric string.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isNumeric($pValue)
    {
        return is_numeric($pValue);
    }

    /**
     * Validate a scalar value.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isScalar($pValue)
    {
        return is_scalar($pValue);
    }

    /**
     * Validate a rewrited string.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isRewritedString($pValue)
    {
        return (is_string($pValue) and preg_match('/^[a-z0-9_-]+$/', $pValue) === 1) ? true : false;
    }

    /**
     * Validate an alpha numeric string.
     *
     * @param mixed $pValue
     * @return bool
     */
    public static function isAlNum($pValue)
    {
        return (is_string($pValue) and preg_match('/^[a-z0-9]+$/', $pValue) === 1) ? true : false;
    }

    /**
     * Validate email.
     *
     * @param string $pValue
     * @return bool
     */
    public static function isEmail($pValue)
    {
        return (filter_var($pValue, FILTER_VALIDATE_EMAIL)) ? true : false;
    }

    /**
     * Regex validation.
     *
     * @param array|string $pValue
     * @param $pExpr PCRE expression
     * @return bool
     */
    public static function isRegex($pValue, $pExpr)
    {
        if (is_array($pValue)) {
            foreach ($pValue as $value) {
                if (! preg_match($pExpr, $value)) {
                    return false;
                }
            }
        } else {
            if (! preg_match($pExpr, $pValue)) {
                return false;
            }
        }

        return true;
    }
}
