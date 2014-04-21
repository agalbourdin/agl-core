<?php
namespace Agl\Core\Request;

use \Agl\Core\Mvc\Controller\Controller,
	\Agl\Core\Observer\Observer,
	\Agl\Core\Url\Url,
	\Exception;

/**
 * Transform the request and save the requested module, view, params...
 *
 * @category Agl_Core
 * @package Agl_Core_Request
 * @version 0.1.0
 */

class Request
{
	/**
	 * HTTP Redirect Codes.
	 */
	const HTTP_CODE_REDIRECT_PERMANENT   = 301;
	const HTTP_CODE_REDIRECT_TEMPORARILY = 302;

	/**
     * 404 HTTP header.
     */
    const HEADER_404 = '404 Not Found';

    /**
     * 500 HTTP header.
     */
    const HEADER_500 = '500 Internal Server Error';

    /**
     * HTML header.
     */
    const HEADER_HTML = 'Content-type: text/html; charset=UTF-8';

    /**
     * JSON header.
     */
    const HEADER_JSON = 'Content-type: application/json; charset=UTF-8';

	/**
	 * Default module.
	 */
	const DEFAULT_MODULE = 'home';

	/**
	 * Default view.
	 */
	const DEFAULT_VIEW = 'index';

	/**
	 * Action parameter name.
	 */
	const ACTION_PARAM = 'action';

	/**
     * The original request URI.
     *
     * @var string
     */
	private $_requestUri = NULL;

	/**
     * The request string.
     *
     * @var string
     */
	private $_request = NULL;

	/**
     * The request as an array.
     *
     * @var array
     */
	private $_requestVars = array();

	/**
     * The requested module.
     *
     * @var string
     */
	private $_module = NULL;

	/**
     * The requested view (url_key)
     *
     * @var string
     */
	private $_view = NULL;

	/**
     * The request params.
     *
     * @var array
     */
	private $_params = array();

	/**
	 * Set HTTP header (404, 401...).
	 *
	 * @param string $pHeader
	 */
	public static function setHttpHeader($pHeader)
	{
		if (! headers_sent()) {
			header($_SERVER['SERVER_PROTOCOL'] . ' ' . $pHeader);
		}
	}

	/**
	 * Set misc. headers (HTML, JSON...).
	 *
	 * @param string $pHeader
	 */
	public static function setHeader($pHeader)
	{
		header($pHeader);
	}

	/**
	 * Initialize the instance by registering the reqest parts.
	 *
	 * @param string $pRequestUri URI to parse
	 */
	public function __construct($pRequestUri)
	{
		if (! $pRequestUri) {
			return $this;
		}

		$this->_requestUri = str_replace(ROOT, DS, $pRequestUri);

		Observer::dispatch(Observer::EVENT_SET_REQUEST_BEFORE, array(
			'request'     => $this,
			'request_uri' => &$this->_requestUri
		));

		$this
			->_setRequest()
			->_setModule()
			->_setView()
			->_setParams();

		Observer::dispatch(Observer::EVENT_SET_REQUEST_AFTER, array(
			'request'     => $this,
			'request_uri' => $this->_requestUri
		));
	}

	/**
	 * Save the request string and an equivalent as array.
	 *
	 * @return Request
	 */
	private function _setRequest()
	{
		if ($this->_requestUri !== NULL) {
            $this->_request = preg_replace('#(^/)|(^' . ROOT . ')|(/$)#', '', $this->_requestUri);

            if (! $this->_request) {
            	$this->_request = self::DEFAULT_MODULE . '/' . self::DEFAULT_VIEW;
            }

            $this->_requestVars = explode('/', $this->_request);
            if (count($this->_requestVars) == 1) {
				$this->_request       .= '/' . self::DEFAULT_VIEW;
				$this->_requestVars[]  = self::DEFAULT_VIEW;
            }

            return $this;
        }

        throw new Exception('Request URI is not available');
	}

	/**
	 * Check the module syntax and save it.
	 *
	 * @return Request|Exception
	 */
	private function _setModule()
	{
		if (preg_match('/^[a-z0-9_-]+$/', $this->_requestVars[0])) {
			$this->_module = $this->_requestVars[0];
        } else {
        	$this->_module = $this->_requestVars[0] = self::DEFAULT_MODULE;
        }

        return $this;
	}

	/**
	 * Check the view syntax and save it.
	 *
	 * @return Request|Exception
	 */
	private function _setView()
	{
		if (preg_match('/^[a-z0-9_-]+$/', $this->_requestVars[1])) {
			$this->_view = $this->_requestVars[1];
        } else {
        	$this->_view = $this->_requestVars[1] = self::DEFAULT_VIEW;
        }

        return $this;
	}

	/**
	 * Save the URL params and sanitize POST and GET values.
	 *
	 * @return Request
	 */
	private function _setParams()
	{
		foreach ($_GET as $key => $value) {
			$this->_params[$key] = $this->_sanitize($value);
		}

		foreach ($_POST as $key => $value) {
			$_POST[$key] = $this->_sanitize($value);
		}

		$nbElements = count($this->_requestVars);
		if ($nbElements > 3) {
			$i = 2;
			while ($i < $nbElements and $i + 1 < $nbElements) {
				if (ctype_digit($this->_requestVars[$i])) {
					$i += 2;
					continue;
				}

				$this->_params[$this->_requestVars[$i]] = $this->_sanitize($this->_requestVars[$i + 1]);
				$i += 2;
			}
		}

		if ($nbElements > 2) {
			$i = 2;
			while ($i < $nbElements) {
				$this->_params[$i - 2] = $this->_sanitize($this->_requestVars[$i]);
				$i++;
			}
		}

		return $this;
	}

	/**
	 * Sanitize the given value.
	 *
	 * @param string $pValue
	 * @return string
	 */
	private function _sanitize($pValue)
	{
		$value = trim($pValue);
		$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
		$value = (string)$value;
		return $value;
	}

	/**
	 * Return the current module.
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->_module;
	}

	/**
	 * Return the current view.
	 *
	 * @return string
	 */
	public function getView()
	{
		return $this->_view;
	}

	/**
	 * Return the requested action, or the default action.
	 *
	 * @return string
	 */
	public function getAction()
	{
		$action = strtolower($this->getParam(self::ACTION_PARAM));
		if ($action === '' or ! preg_match('/^[a-z0-9_-]+$/', $action)) {
			return Controller::DEFAULT_ACTION;
		}

		return $action;
	}

	/**
	 * Return the current request string.
	 *
	 * @return string
	 */
	public function getReq()
	{
		return $this->_request;
	}

	/**
	 * Return the current request array.
	 *
	 * @return string
	 */
	public function getReqVars()
	{
		return $this->_requestVars;
	}

	/**
	 * Return all the request parameters.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Return the $pParam parameter, if exists.
	 *
	 * @return string
	 */
	public function getParam($pParam)
	{
		if (isset($this->_params[$pParam])) {
			return $this->_params[$pParam];
		}

		return '';
	}

	/**
	 * Return the POST or a POST entry if $pParam is specified.
	 *
	 * @param string $pParam
	 * @return string
	 */
	public function getPost($pParam = '')
	{
        if (empty($pParam)) {
        	return $_POST;
        } else if (isset($_POST[$pParam])) {
        	return $_POST[$pParam];
        }

        return '';
	}

	/**
	 * Redirect the user to another page.
	 * $pPath could be an AGL formated URL (module/view) or * / to redirect to
	 * the current module, or * / * / to redirect to the current module and
	 * to the current view.
	 *
	 * @param string $pPath
	 * @param array $pParams
	 * @param int $pType Type of redirection
	 */
	public static function redirect($pPath = '', array $pParams = array(), $pType = self::HTTP_CODE_REDIRECT_TEMPORARILY)
	{
		if ($pPath === '' or preg_match('#^[a-z0-9-_\*]+/[a-z0-9-_\*]+(/)?$#', $pPath)) {
			header('Location: ' . Url::get($pPath, $pParams), true, $pType);
		} else {
			header('Location: ' . $pPath, true, $pType);
		}

		exit;
	}

	/**
	 * Indicate if a request was made by Ajax.
	 *
	 * @return bool
	 */
	public static function isAjax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
				and strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0) ? true : false;
	}
}
