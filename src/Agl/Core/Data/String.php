<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate strings.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class String
{
    /**
     * Create an URL-friendly string.
     *
     * @param string $pString Original string
     * @return string Rewrited string
     */
    public static function rewrite($pString)
    {
        $specialChars = Array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'ß', 'Ç', 'ç', 'Ð', 'ð', 'Ñ', 'ñ', 'Þ', 'þ', 'Ý', '', 'œ');
        $normalChars  = Array('A', 'A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'B', 'C', 'c', 'D', 'd', 'N', 'n', 'P', 'p', 'Y', 'E', 'oe');

        $string = str_replace($specialChars, $normalChars, $pString);
        $string = preg_replace('/\b([a-z0-9]{1,2})\b/i', '', $string);
        $string = preg_replace('/[^A-Za-z0-9]+/', '-', $string);
        $string = strip_tags($string);
        $string = trim($string, '-');
        $string = strtolower($string);
        $string = preg_replace('/(-[0-9]+)$/', '', $string);

        return $string;
    }

    /**
    * Create a camel-case string.
    *
    * @param string $pStr Chaîne au format classique (avec "_")
    * @param bool $pCapitaliseFirstChar Make the first char uppercase
    * @return string Camel-case string
    */
    public static function toCamelCase($pStr, $pCapitaliseFirstChar = true)
    {
        if (empty($pStr)) {
            return $pStr;
        }

        $pStr = strtolower($pStr);

        if ($pCapitaliseFirstChar) {
            $pStr[0] = strtoupper($pStr[0]);
        }

        return preg_replace('/_([a-z])/e', "strtoupper('\\1')", $pStr);
    }

    /**
     * Revert a camel-case.
     *
     * @param string $pStr Camel-case string
     * @return string Converted string
     */
    public static function fromCamelCase($pStr)
    {
        if (empty($pStr)) {
            return $pStr;
        }

        $pStr[0] = strtolower($pStr[0]);
        return preg_replace('/([A-Z])/e', "'_' . strtolower('\\1')", $pStr);
    }

    /**
     * Return a random string with a variable length and strength.
     *
     * @param int $pLength Length of the string to generate
     * @param int $pStrength Strength of the string to generate
     * @return string Random string
     */
    public static function getRandomString($pLength = 6, $pStrength = 1)
    {
    	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	if ($pStrength >= 1) {
            $chars .= '0123456789';
    	}

    	if ($pStrength >= 2) {
            $strengthChars = '@#$%';
            $chars        .= $strengthChars;
    	}

    	$str = '';
    	for ($i = 0; $i < $pLength; $i++) {
            $str .= $chars[(rand() % strlen($chars))];
    	}

        if ($pStrength >= 2 and ! preg_match('/@|#|\$|%/', $str)) {
            $str = substr($str, 0, -1);
            $str = preg_replace("/^(.{" . (rand() % $pLength) ."})/", "$1" . $strengthChars[rand() % strlen($strengthChars)], $str);
        }

    	return $str;
    }

    /**
     * Truncate a string and add an optional suffix.
     *
     * @param string $pStr String to truncate
     * @param int $pLimit Length of the string to return
     * @param string $pEnd Suffix
     */
    public static function truncate($pStr, $pLimit, $pEnd = '...')
    {
        $str    = strip_tags($pStr);
        $length = strlen($str);
        if ($length <= $pLimit) {
            return $str;
        }

        return trim(substr($str, 0, $pLimit)) . $pEnd;
    }

}
