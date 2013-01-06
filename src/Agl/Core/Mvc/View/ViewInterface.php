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
	const APP_PHP_VIEW_DIR = 'View';

    /**
     *  The default Web Modules directory.
     */
    const APP_HTTP_VIEW_DIR = 'views';

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
     * HTTP layouts directory.
     */
    const APP_HTTP_TEMPLATE_DIR = 'app/template';

    public function setFile($pFile);
    public function startBuffer();
    public function getBuffer($pBuffer);
    public function render();
    public function getBlock($pBlock);
    public function getType();
}
