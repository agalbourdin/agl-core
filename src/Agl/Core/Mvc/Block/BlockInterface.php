<?php
namespace Agl\Core\Mvc\Block;

/**
 * Interface - BLock
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Block
 * @version 0.1.0
 */

interface BlockInterface
{
	/**
     * The suffix used in the application's Blocks class names.
     */
    const APP_BLOCK_SUFFIX = 'Block';

    /**
     * The application's block directory.
     */
    const APP_PHP_BLOCK_DIR = 'block';

    /**
     * The application's HTTP block directory.
     */
    const APP_HTTP_BLOCK_DIR = 'block';

    /**
     * Prefix for the cache files.
     */
    const CACHE_FILE_PREFIX = 'block_';

    public static function getCacheInfo(array $blockPathInfos, array $pBlockConfig);
    public static function isCacheEnabled($pBlockConfig);
    public static function checkAcl($pGroupId, $pBlockId);
    public function setFile($pFile);
    public function setVars(array $pVars = array());
    public function getView();
    public function render();
}
