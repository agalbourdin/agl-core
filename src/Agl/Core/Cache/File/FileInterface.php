<?php
namespace Agl\Core\Cache\File;

/**
 * Interface - File
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_File
 * @version 0.1.0
 * @deprecated
 */

interface FileInterface
{
	/**
	 * The directory name where to save the cache files.
	 */
    const AGL_VAR_CACHE_DIR = 'cache';

    /**
     * The extension to use for the cache files.
     */
    const AGL_VAR_CACHE_EXT = '.cache';

    /**
     * The tag returned when the requested cache path doesn't exist.
     */
    const AGL_CACHE_TAG_NOT_FOUND = '_agl_not_found';

    /**
     * Separator for the cache files name, between module and view.
     */
    const CACHE_FILE_SEPARATOR = '_';

    public function __construct($pIdentifier, $pTtl = 0, $pPath = '');
    public function getIdentifier();
    public function getPath();
    public function getFile();
    public function getFullPath();
    public function save();
}
