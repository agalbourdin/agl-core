<?php
namespace Agl\Core\Cache\File;

use \Agl\Core\Agl,
	\Agl\Core\Data\Dir as DirecoryData,
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
	 * Absolute path to the cache directory.
	 *
	 * @var null|string
	 */
	protected $_path = NULL;

	/**
	 * Create the Cache instance, set the identifier and create the cache file
	 * and directories.
	 *
	 * @param string $pIdentifier
	 * @param int $pTtl Cache Time to Live in seconds, 0 = never expires
	 * @param string $pPath Absolute path to the cache directory
	 */
	public function __construct($pIdentifier, $pTtl = 0, $pPath = '')
	{
		Agl::validateParams(array(
			'RewritedString' => $pIdentifier,
			'String'         => $pPath,
			'Int'            => $pTtl
        ));

		$this->_identifier = $pIdentifier;

		$this->_setPath($pPath)
		     ->_checkTtl($pTtl)
		     ->_createFile();
	}

	/**
	 * Create the cache file and directories.
	 *
	 * @return FileAbstract
	 */
	protected function _createFile()
	{
		$dir = $this->getPath();
		if (! DirecoryData::create($dir)) {
			throw new Exception("Unable to create the cache directory '$dir'");
		}

		$file = $dir . $this->getFile();
		if (! FileData::create($file)) {
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
		$file = $this->getFullPath();
		if ($pTtl and is_writable($file) and (time() - filemtime($file)) > $pTtl) {
			FileData::write($file, '');
		}

		return $this;
	}

	/**
     * Set the cache directory.
     *
     * @param string $pPath Absolute path to the cache directory
     */
    protected function _setPath($pPath)
    {
    	if (! $pPath) {
    		$this->_path = APP_PATH
				              . Agl::APP_VAR_DIR
				              . static::AGL_VAR_CACHE_DIR
				              . DS
				              . FileData::getSubPath(md5($this->getFile()));
    	} else {
    		$this->_path = $pPath;
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
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Return the cache filename.
     *
     * @return string
     */
	public function getFile()
    {
        return $this->_identifier
               . static::AGL_VAR_CACHE_EXT;
    }

    /**
     * Return the cache file path.
     *
     * @return string
     */
    public function getFullPath()
    {
    	return $this->getPath() . $this->getFile();
    }
}
