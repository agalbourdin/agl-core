<?php
namespace Agl\Core\Mvc\View;

/**
 * Interface - View
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View
 * @version 0.1.0
 */

interface ViewInterface
{
    /**
     *View type : HTML.
     */
    const TYPE_HTML = 'html';

    /**
     *View type : JSON.
     */
    const TYPE_JSON = 'json';

    /**
     * The suffix used by the application's View class names.
     */
    const APP_VIEW_SUFFIX = 'View';

    /**
	 * The application directory to search a View class.
	 */
	const APP_PHP_DIR = 'view';

    /**
     *  The default Web Modules directory.
     */
    const APP_HTTP_DIR = 'view';

    /**
     *  The Web module config file name.
     */
    const CONFIG_FILE = 'layout';

    /**
     * The application's skin directory.
     */
    const APP_HTTP_SKIN_DIR = 'skin';

    /**
     * Marker identifier, used to replace some portions of code after the
     * template rendering.
     */
    const VIEW_MARKER = 'AGL_VIEW_MARKER::';

    /**
     * Prefix for the cache files.
     */
    const CACHE_FILE_PREFIX = 'view_';

    /**
     * 404 error page path.
     */
    const ERROR_404 = 'error/404';

    /**
     * Static error page path.
     */
    const ERROR_STATIC = 'public/error_static.phtml';

    /**
     * 403 error page path.
     */
    const ERROR_403 = 'error/auth';

    public function setFile($pFile);
    public function startBuffer();
    //public function getBuffer($pBuffer);
    public function render();
    public function getBlock($pBlock);
    public function getType();
}
