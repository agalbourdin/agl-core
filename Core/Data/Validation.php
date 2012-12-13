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
    public static $_types = array(
        'Bool'           => true,
        'Int'            => true,
        'Double'         => true,
        'String'         => true,
        'StrictString'   => true,
        'Null'           => true,
        'Digit'          => true,
        'Numeric'        => true,
        'Scalar'         => true,
        'RewritedString' => true,
        'AlNum'          => true,
        'Email'          => true
    );

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
            $func = '_validate' . $type;
            if (! isset(self::$_types[$type])) {
                throw new \Agl\Exception("TypeHint validation failed, unknown type '$type'");
            }

            $validation = self::$func($value);
            if ($validation) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * Validate a boolean.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateBool($pValue)
    {
        return is_bool($pValue);
    }

    /**
     * Validate an integer.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateInt($pValue)
    {
        return is_int($pValue);
    }

    /**
     * Validate a double (float).
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateDouble($pValue)
    {
        return is_float($pValue);
    }

    /**
     * Validate a string.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateString($pValue)
    {
        return (is_string($pValue) or is_int($pValue) or is_float($pValue));
    }

    /**
     * Validate a string.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateStrictString($pValue)
    {
        return is_string($pValue);
    }

    /**
     * Validate a NULL value.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateNull($pValue)
    {
        return ($pValue === NULL);
    }

    /**
     * Validate a digit string.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateDigit($pValue)
    {
        return ctype_digit($pValue);
    }

    /**
     * Validate a numeric string.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateNumeric($pValue)
    {
        return is_numeric($pValue);
    }

    /**
     * Validate a scalar value.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateScalar($pValue)
    {
        return is_scalar($pValue);
    }

    /**
     * Validate a rewrited string.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateRewritedString($pValue)
    {
        return preg_match('/^[a-z0-9_-]+$/', $pValue);
    }

    /**
     * Validate an alpha numeric string.
     *
     * @param mixed $pValue
     * @return bool
     */
    private static function _validateAlNum($pValue)
    {
        return preg_match('/^[a-z0-9]+$/', $pValue);
    }

    /**
     * Validate email.
     *
     * @param string $pValue
     * @return bool
     */
    private static function _validateEmail($pValue)
    {
        return (filter_var($pValue, FILTER_VALIDATE_EMAIL)) ? true : false;
    }
}
