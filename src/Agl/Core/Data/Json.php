<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate JSON data.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Json
{
    /**
     * The parsed content of the JSON string/file.
     *
     * @var stdClass
     */
    private $_content = NULL;

    /**
     * Decode a JSON string and save it to the $_content variable.
     *
     * @param $pString string JSON encoded string
     * @param $pToArray bool Get result as array
     * @return Json
     */
    public function loadString($pString, $pToArray = false)
    {
        $this->_content = ($pToArray) ? json_decode($pString, true) : json_decode($pString);
        return $this;
    }

    /**
     * Load a JSON file, decode the data and save it to the $_content variable.
     *
     * @param $pFile string File path
     * @param $pToArray bool Get result as array
     * @return Json
     */
    public function loadFile($pFile, $pToArray = false)
    {
        $content = file_get_contents($pFile);
        if ($content !== false) {
            $this->_content = ($pToArray) ? json_decode($content, true) : json_decode($content);
        }

        return $this;
    }

    /**
     * Return the parsed content.
     *
     * @return stdClass
     */
    public function getContent()
    {
        return $this->_content;
    }
}
