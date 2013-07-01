<?php
namespace Agl\Core\Cache;

use \Agl\Core\Agl,
    \Agl\Core\Data\Dir as DirecoryData,
    \Agl\Core\Data\File as FileData,
    \Exception;

/**
 * Cahe management as PHP array.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File_Format
 * @version 0.1.0
 */

class File
    implements CacheInterface
{
    /**
     * Create the requested cache file and directories.
     *
     * @param string $pFile File path
     * @return File
     */
    private function _createFile($pFile)
    {
        $pathInfo = pathinfo($pFile);

        $dir = $pathInfo['dirname'];
        if (! DirecoryData::create($dir)) {
            throw new Exception("Unable to create the cache directory '$dir'");
        }

        if (! FileData::create($pFile)) {
            throw new Exception("Unable to create the cache file '$pFile'");
        }

        return $this;
    }

    /**
     * Check if the TTL is set and if the cache has expired.
     *
     * @param int $pTtl Cache Time to Live in seconds, 0 = never expires
     * @return File
     */
    private function _checkTtl($pTtl)
    {
        $file = $this->getFullPath();
        if ($pTtl and is_writable($file) and (time() - filemtime($file)) > $pTtl) {
            FileData::write($file, '');
        }

        return $this;
    }

    private function _loadFile($pKey)
    {
        $file = $this->_getFile($pKey);
        if (array_key_exists($file, $this->_files)) {
            return $file;
        }

        $this->_createFile($file);
        $this->_files[$file] = json_decode(file_get_contents($file), true);

        if ($this->_files[$file] === NULL) {
            $this->_files[$file] = array();
        }

        return $file;
    }

    /**
     * Return the cache file path.
     *
     * @param string $pKey
     * @return string
     */
    private function _getFile($pKey)
    {
        if (strpos($pKey, '.') !== false) {
            $keyArr   = explode('.', $pKey);
            $fileName = md5($keyArr[0]);
        } else {
            $fileName = md5($pKey);
        }

        return APP_PATH
             . Agl::APP_VAR_DIR
             . static::AGL_VAR_CACHE_DIR
             . DS
             . FileData::getSubPath($fileName)
             . $fileName
             . static::AGL_VAR_CACHE_EXT;
    }

    /**
     * Opened cached files.
     *
     * @var array
     */
    private $_files = array();

    /**
     * Set a value to the cached array.
     *
     * @param string $pKey The value key
     * @param mixed $pValue The value to save
     * @param int $pTtl Cache Time To Live (seconds)
     *
     * @return File
     */
    public function set($pKey, $pValue, $pTtl = 0)
    {
        Agl::validateParams(array(
            'String' => $pKey
        ));

        $file = $this->_loadFile($pKey);

        if (array_key_exists($pKey, $this->_files[$file])) {

        } else {
            $this->_files[$file][$pKey] = array(
                'expire' => ($pTtl) ? (time() + $pTtl) : $pTtl,
                'value'  => $pValue
            );
        }

        var_dump($this->_files);

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

    public function has($pKey)
    {

    }

    /**
     * Unset a value from the cache.
     *
     * @param string $pKey
     * @return File
     */
    public function remove($pKey)
    {
        if (array_key_exists($pKey, $this->_array)) {
            unset($this->_array[$pKey]);
        }

        return $this;
    }
}
