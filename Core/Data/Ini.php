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
     * The INI file (absolute path).
     *
     * @var null|string
     */
    private $_file = NULL;

    /**
     * The parsed content of the INI file.
     *
     * @var array
     */
    private $_content = array();

    /**
     * Set the INI file path when the class is instanciated.
     *
     * @var string $pFile Absolute path to the INI file
     */
    public function __construct($pFile)
    {
        $this->_file = $pFile;
    }

    /**
     * Parse the keys of the loaded content to create multidimensional arrays,
     * based on the SEPARATOR constant (recursive).
     *
     * @param array $content The array to parse
     * @return Ini
     */
    private function _parseKeys(array &$content)
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
                    $this->_parseKeys($value);
                }

                $arr = $value;

                unset($content[$key]);
            } else if (is_array($value)) {
                $arr = &$content[$key];
                $this->_parseKeys($value);
                $arr = $value;
            }
        }

        return $this;
    }

    /**
     * Load the INI file content into the _content variable, as a
     * multidimensional array.
     *
     * @var bool $pParseKeys Parse the keys to create multidimensional arrays
     * @return Ini
     */
    public function loadFile($pParseKeys = false)
    {
        $this->_content = parse_ini_file($this->_file, true);

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
