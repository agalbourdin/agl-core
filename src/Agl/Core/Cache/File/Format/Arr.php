<?php
namespace Agl\Core\Cache\File\Format;

use \Agl\Core\Agl,
    \Agl\Core\Cache\File\FileAbstract,
    \Agl\Core\Cache\File\FileInterface,
    \Agl\Core\Data\File as FileData,
    \Exception;

/**
 * Cahe management as PHP array.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File_Format
 * @version 0.1.0
 */

class Arr
    extends FileAbstract
        implements FileInterface
{
    /**
     * The cached array.
     *
     * @var array
     */
    private $_array = array();

    /**
     * Call the parent's constructor to set the cache identifier.
     * Retrieve the cache content and save it.
     *
     * @param string $pIdentifier
     * @param int $pTtl Cache Time to Live in seconds, 0 = never expires
     * @param string $pPath Absolute path to the cache directory
     */
    public function __construct($pIdentifier, $pTtl = 0, $pPath = '')
    {
        parent::__construct($pIdentifier, $pTtl, $pPath);

        $content = file_get_contents($this->getFullPath());
        if ($content) {
            $this->_array = json_decode($content, true);
        }

        if (! is_array($this->_array)) {
            throw new Exception("Corrupted array cache '$pIdentifier'");
        }
    }

    /**
     * If required, save the cached array to the cache file.
     *
     * @return Arr
     */
    public function save()
    {
        if (FileData::write($this->getFullPath(), json_encode($this->_array)) === false) {
            throw new Exception("Unable to write the cache");
        }

        return $this;
    }

    /**
     * Get the value corresponding to the key $pKey in the cached array.
     *
     * @param string $pKey
     * @return mixed
     */
    public function get($pKey)
    {
        if (array_key_exists($pKey, $this->_array)) {
            return $this->_array[$pKey];
        }

        return static::AGL_CACHE_TAG_NOT_FOUND;
    }

    /**
     * Set a value to the cached array.
     *
     * @param string $pKey The value key
     * @param mixed $pValue The value to save
     *
     * @return Arr
     */
    public function set($pKey, $pValue)
    {
        Agl::validateParams(array(
            'String' => $pKey
        ));

        $this->_array[$pKey] = $pValue;

        return $this;
    }

    /**
     * Unset a value from the cache.
     *
     * @param string $pKey
     * @return mixed
     */
    public function remove($pKey)
    {
        if (array_key_exists($pKey, $this->_array)) {
            unset($this->_array[$pKey]);
        }

        return $this;
    }
}
