<?php
namespace Agl\Core\Cache\File\Format;

use \Agl\Core\Agl,
    \Agl\Core\Cache\File\FileAbstract,
    \Agl\Core\Cache\File\FileInterface,
    \Agl\Core\Data\File as FileData,
    \Exception;

/**
 * Cahe management for raw content storage.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File_Format
 * @version 0.1.0
 * @deprecated
 */

class Raw
    extends FileAbstract
        implements FileInterface
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
     * @param string $pPath Absolute path to the cache directory
     */
    public function __construct($pIdentifier, $pTtl = 0, $pPath = '')
    {
        parent::__construct($pIdentifier, $pTtl, $pPath);

        $this->_content = file_get_contents($this->getFullPath());
    }

    /**
     * Set the _save variable to true.
     *
     * @return Raw
     */
    public function save()
    {
        if (FileData::write($this->getFullPath(), $this->_content) === false) {
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
    public function get()
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
    public function set($pContent)
    {
        Agl::validateParams(array(
            'String' => $pContent
        ));

        $this->_content = $pContent;

        return $this;
    }
}
