<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate an INI file.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Ini
{
    /**
     * Separator used to define multidimensional arrays.
     */
    const SEPARATOR = '.';

    /**
     * The parsed content of the INI file.
     *
     * @var array
     */
    private $_content = array();

    /**
     * Parse the keys of the loaded content to create multidimensional arrays,
     * based on the SEPARATOR constant (recursive).
     *
     * @param array $content The array to parse
     */
    private static function _parseKeys(array &$content)
    {
        foreach($content as $key => $value) {
            if (strpos($key, self::SEPARATOR) !== false) {
                $pieces = explode(self::SEPARATOR, $key);

                $arr = &$content;
                foreach($pieces as $piece) {
                    if (! isset($arr[$piece])) {
                        $arr[$piece] = '';
                    }
                    $arr = &$arr[$piece];
                }

                if (is_array($value)) {
                    self::_parseKeys($value);
                }

                $arr = $value;

                unset($content[$key]);
            } else if (is_array($value)) {
                $arr = &$content[$key];
                self::_parseKeys($value);
                $arr = $value;
            }
        }
    }

    /**
     * Load the INI file content into the _content variable, as a
     * multidimensional array.
     *
     * @var bool $pParseKeys Parse the keys to create multidimensional arrays
     * @return Ini
     */
    public function loadFile($pFile, $pParseKeys = false)
    {
        $this->_content = parse_ini_file($pFile, true);

        if ($pParseKeys) {
            $this->_parseKeys($this->_content);
        }
        return $this;
    }

    /**
     * Return the parsed content.
     *
     * @return array
     */
    public function getContent()
    {
        return $this->_content;
    }
}
