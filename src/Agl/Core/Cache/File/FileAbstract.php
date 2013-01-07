<?php
namespace Agl\Core\Cache\File;

use \Agl\Core\Agl,
	\Agl\Core\Data\Directory as DirecoryData,
	\Agl\Core\Data\File as FileData,
	\Exception;

/**
 * Abstract class - File
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File
 * @version 0.1.0
 */

abstract class FileAbstract
{
	/**
	 * The cache identifier.
	 *
	 * @var string
	 */
	protected $_identifier = NULL;

	/**
	 * Create the Cache instance, set the identifier and create the cache file
	 * and directories.
	 *
	 * @param string $pIdentifier
	 * @param int $pTtl Cache Time to Live in seconds, 0 = never expires
	 */
	public function __construct($pIdentifier, $pTtl = 0)
	{
		Agl::validateParams(array(
			'RewritedString' => $pIdentifier,
			'Int'            => $pTtl
        ));

		$this->_identifier = $pIdentifier;

		$this->_checkTtl($pTtl);
		$this->_createFile();
	}

	/**
	 * Create the cache file and directories.
	 *
	 * @return FileAbstract
	 */
	protected function _createFile()
	{
		$dir = $this->getCachePath();
		if (! DirecoryData::createDir($dir)) {
			throw new Exception("Unable to create the cache directory '$dir'");
		}

		$file = $dir . $this->getCacheFile();
		if (! FileData::createEmptyFile($file)) {
			throw new Exception("Unable to create the cache file '$file'");
		}

		return $this;
	}

	/**
	 * Check if the TTL is set and if the cache has expired.
	 *
	 * @param int $pTtl Cache Time to Live in seconds, 0 = never expires
	 * @return FileAbstract
	 */
	protected function _checkTtl($pTtl)
	{
		$file = $this->getCacheFullPath();
		if ($pTtl and is_writable($file) and (time() - filemtime($file)) > $pTtl) {
			file_put_contents($file, '');
		}

		return $this;
	}

	/**
	 * Rerturn the Cache identifier.
	 *
	 * @return string
	 */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Return the cache path (absolute directory).
     *
     * @return string
     */
    public function getCachePath()
    {
        return Agl::app()->getPath()
               . Agl::APP_VAR_DIR
               . DS
               . static::AGL_VAR_CACHE_DIR
               . FileData::getSubPath(md5($this->getCacheFile()));
    }

    /**
     * Return the cache filename.
     *
     * @return string
     */
	public function getCacheFile()
    {
        return $this->_identifier
               . static::AGL_VAR_CACHE_EXT;
    }

    /**
     * Return the cache file path.
     *
     * @return string
     */
    public function getCacheFullPath()
    {
    	return $this->getCachePath() . $this->getCacheFile();
    }
}
