<?php
namespace Agl\Core\Cache\File\Format;

use \Agl,
    \Agl\Core\Cache\File\FileAbstract,
    \Agl\Core\Cache\File\FileInterface,
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
     */
    public function __construct($pIdentifier, $pTtl = 0)
    {
        parent::__construct($pIdentifier, $pTtl);

        $content = file_get_contents($this->getCacheFullPath());
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
        if (file_put_contents($this->getCacheFullPath(), json_encode($this->_array), LOCK_EX) === false) {
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
    public function getValue($pKey)
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
    public function setValue($pKey, $pValue)
    {
        Agl::validateParams(array(
            'StrictString' => $pKey
        ));

        $this->_array[$pKey] = $pValue;

        return $this;
    }
}