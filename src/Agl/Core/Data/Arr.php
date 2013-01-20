<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate arrays.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Arr
{
    /**
     * Search a value in a multidimensional array.
     *
     * @param $pValue string The searched value
     * @param $pArray array The array
     * @return mixed
     */
    public static function arraySearch($pValue, array $pArray)
    {
        foreach($pArray as $key => $value) {
            $currentKey = $key;
            if ($pValue === $value or (is_array($value) and self::arraySearch($pValue, $value) !== false)) {
                return $currentKey;
            }
        }

        return false;
    }
}
