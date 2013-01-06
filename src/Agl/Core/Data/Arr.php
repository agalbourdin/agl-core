<?php
namespace Agl\Core\Data;

use \Agl;

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
     * Search a value in a multidimensional array and returns its key if finded.
     *
     * @param $pValue string The searched value
     * @param $pArray array The array
     * @return mixed
     * @todo Recursive search / return array of keys
     */
    public static function arraySearch($pValue, array $pArray)
    {
        Agl::validateParams(array(
            'StrictString' => $pValue
        ));

        if (empty($pValue) or empty($pArray)) {
            return false;
        }

        foreach ($pArray as $key => $subArr) {
            if (array_search($pValue, $subArr)) {
                return $key;
            }
        }

        return false;
    }
}
