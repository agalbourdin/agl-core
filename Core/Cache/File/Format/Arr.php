<?php
namespace Agl\Core\Cache\File\Format;

/**
 * Cahe management as PHP array.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File_Format
 * @version 0.1.0
 */

class Arr
    extends \Agl\Core\Cache\File\FileAbstract
        implements \Agl\Core\Cache\File\FileInterface
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
            eval('$this->_array = ' . file_get_contents($this->getCacheFullPath()) . ';');
        }

        if (! is_array($this->_array)) {
            throw new \Agl\Exception("Corrupted array cache '$pIdentifier'");
        }
    }

    /**
     * If required, save the cached array to the cache file.
     *
     * @return Arr
     */
    public function save()
    {
        if (file_put_contents($this->getCacheFullPath(), var_export($this->_array, true), LOCK_EX) === false) {
            throw new \Agl\Exception("Unable to write the cache");
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

        return \Agl\Core\Cache\File\FileInterface::AGL_CACHE_TAG_NOT_FOUND;
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
        \Agl::validateParams(array(
            'StrictString' => $pKey
        ));

        $this->_array[$pKey] = $pValue;

        return $this;
    }
}
