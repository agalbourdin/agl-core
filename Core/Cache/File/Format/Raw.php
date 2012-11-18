<?php
namespace Agl\Core\Cache\File\Format;

/**
 * Cahe management for raw content storage.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File_Format
 * @version 0.1.0
 */

class Raw
    extends \Agl\Core\Cache\File\FileAbstract
        implements \Agl\Core\Cache\File\FileInterface
{
    /**
     * The cached content.
     *
     * @var array
     */
    private $_content = NULL;

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

        $this->_content = file_get_contents($this->getCacheFullPath());
    }

    /**
     * Set the _save variable to true.
     *
     * @return Raw
     */
    public function save()
    {
        if (file_put_contents($this->getCacheFullPath(), $this->_content, LOCK_EX) === false) {
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
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Set a value to the cached array.
     *
     * @param string $pKey The value key
     * @param mixed $pValue The value to save
     *
     * @return Arr
     */
    public function setContent($pContent)
    {
        \Agl::validateParams(array(
            'StrictString' => $pContent
        ));

        $this->_content = $pContent;

        return $this;
    }
}
