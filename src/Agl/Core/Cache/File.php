<?php
namespace Agl\Core\Cache;

use \Agl\Core\Agl,
    \Agl\Core\Data\Dir as DirecoryData,
    \Agl\Core\Data\File as FileData,
    \Exception;

/**
 * Cache management as PHP array.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache
 * @version 0.1.0
 */

class File
    implements CacheInterface
{
    /**
     * The directory name where to save cache files (file type only).
     */
    const AGL_VAR_CACHE_DIR = 'cache';

    /**
     * The extension to use for cache files (file type only).
     */
    const AGL_VAR_CACHE_EXT = '.cache';

    /**
     * Files default content (when created).
     */
    const DEFAULT_CONTENT = '[]';

    /**
     * Number of subdirectories to create to store a cache file.
     */
    const SUB_DIRS = 3;

    /**
     * Opened cached files.
     *
     * @var array
     */
    private $_files = array();

    /**
     * Return the cache file path.
     *
     * @param string $pKey
     * @return string
     */
    private static function _getFilePath($pKey)
    {
        if (strpos($pKey, static::SECTION_DELIMITER) !== false) {
            $keyArr   = explode(static::SECTION_DELIMITER, $pKey, 2);
            $fileName = md5($keyArr[0]);
        } else {
            $fileName = md5($pKey);
        }

        return APP_PATH
             . Agl::APP_VAR_DIR
             . self::AGL_VAR_CACHE_DIR
             . DS
             . FileData::getSubPath($fileName, self::SUB_DIRS)
             . $fileName
             . self::AGL_VAR_CACHE_EXT;
    }

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

        if (! FileData::create($pFile, self::DEFAULT_CONTENT)) {
            throw new Exception("Unable to create the cache file '$pFile'");
        }

        return $this;
    }

    /**
     * Remove cache entry if expired.
     *
     * @param string $pFile
     * @param string $pKey
     * @return bool
     */
    private function _checkTtl($pFile, $pKey)
    {
        if ($this->_files[$pFile][$pKey][static::AGL_CACHE_EXPIRE] < time()) {
            unset($this->_files[$pFile][$pKey]);
            $this->_save($pFile);

            return false;
        }

        return true;
    }

    /**
     * Load the requested cache file, and create it if required.
     *
     * @param string $pKey
     * @return string File path
     */
    private function _loadFile($pKey)
    {
        $file = self::_getFilePath($pKey);
        if (isset($this->_files[$file])) {
            return $file;
        }

        $this->_createFile($file);
        $this->_files[$file] = json_decode(file_get_contents($file), true);

        return $file;
    }

    /**
     * Save cache of the specified file.
     *
     * @param string $pFile
     * @return bool
     */
    private function _save($pFile)
    {
        if (isset($this->_files[$pFile])) {
            return FileData::write($pFile, json_encode($this->_files[$pFile]));
        }
    }

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
        $file = $this->_loadFile($pKey);

        $previousValue = $this->get($pKey);

        $this->_files[$file][$pKey] = array(
            static::AGL_CACHE_EXPIRE => ($pTtl) ? (time() + $pTtl) : $pTtl,
            static::AGL_CACHE_VALUE  => $pValue
        );

        if ($previousValue !== $pValue) {
            $this->_save($file);
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
        $file = $this->_loadFile($pKey);

        if (isset($this->_files[$file][$pKey]) and $this->_checkTtl($file, $pKey)) {
            return $this->_files[$file][$pKey][static::AGL_CACHE_VALUE];
        }

        return NULL;
    }

    /**
     * Check if key exists in cache (a key is considered existing when its value
     * is NULL).
     *
     * @param string $pKey
     * @return bool
     */
    public function has($pKey)
    {
        $file = $this->_loadFile($pKey);

        if (isset($this->_files[$file][$pKey]) and $this->_checkTtl($file, $pKey)) {
            return true;
        }

        return false;
    }

    /**
     * Unset a value from the cache.
     *
     * @param string $pKey
     * @return File
     */
    public function remove($pKey)
    {
        $file = $this->_loadFile($pKey);

        if (isset($this->_files[$file][$pKey])) {
            unset($this->_files[$file][$pKey]);
            $this->_save($file);
        }

        return $this;
    }

    /**
     * Flush the entire cache or a cache section.
     *
     * @param string $pSection
     * @return File
     */
    public function flush($pSection = '')
    {
        if ($pSection) {
            $file = self::_getFilePath($pSection);
            FileData::delete($file);

            $path = realpath(dirname($file));
            for ($i = 0; $i < self::SUB_DIRS; $i++) {
                $current = $path;
                $path    = realpath($path . '/../');
                if (count(glob($current . '/*')) === 0) {
                    DirecoryData::delete($current);
                }
            }
        } else {
            DirecoryData::deleteRecursive(
                APP_PATH
                . Agl::APP_VAR_DIR
                . self::AGL_VAR_CACHE_DIR
            );
        }

        return $this;
    }
}
