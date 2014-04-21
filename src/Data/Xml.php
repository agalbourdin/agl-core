<?php
namespace Agl\Core\Data;

use \DOMAttr,
    \DOMDocument,
    \DOMElement,
    \DOMNodeList,
    \DOMText,
    \DOMXPath,
    \Exception;

/**
 * Generic methods to manipulate a DomDocument.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Xml
{
    /**
     * The generated DOMDocument.
     *
     * @var DOMDocument
     */
    private $_domDoc = NULL;

    /**
     * The generated DOMXPath
     *
     * @var DOMXPath
     */
    private $_xPath = NULL;

    /**
     * Create a new DOMDocument.
     */
    public function __construct()
    {
        $this->_domDoc = new DOMDocument();
        $this->_domDoc->preserveWhiteSpace = true;
        $this->_domDoc->encoding = 'UTF-8';
        $this->_domDoc->xmlVersion = '1.0';
    }

    /**
     * Create a new DOMXPath.
     *
     * @return DOMXPath
     */
    private function _getXPath()
    {
        if ($this->_xPath === NULL) {
            $this->_xPath = new DOMXPath($this->_domDoc);
        }

        return $this->_xPath;
    }

    /**
     * Parse the required config path in order to convert the Agl syntax into a
     * valid XPath query.
     *
     * @param string $pPath Requested path
     * @return string XPath query
     */
    public function parseXPath($pPath)
    {
        $path = str_replace('@app', '/', $pPath);
        $path = preg_replace('#^@module\[([a-z0-9/]+)\]|@layout#', '', $path);

        $pathArr = explode('/', $path);

        foreach($pathArr as $key => $node) {
            if (preg_match('/^([a-zA-Z0-9\[\]@\'"=_-]+):([a-zA-Z0-9_-]+)$/', $node, $matches)) {
                $pathArr[$key] = $matches[1] . '/attribute::' . $matches[2];
            } else if (preg_match('/^([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)=([a-zA-Z0-9_-]+)$/', $node, $matches)) {
                $pathArr[$key] = $matches[1] . '[@' . $matches[2] . '="' . $matches[3] . '"]';
            } else if (preg_match('/^([a-zA-Z0-9_-]+)>([a-zA-Z0-9_-]+)=([a-zA-Z0-9_-]+)$/', $node, $matches)) {
                $pathArr[$key] = $matches[1] . '[' . $matches[2] . '="' . $matches[3] . '"]';
            }
        }

        return '//' . implode('/', $pathArr);
    }

    /**
     * Load an XML file into the DOMDocument.
     *
     * @param string $pFile File to load
     * @return mixed
     */
    public function loadFromXmlFile($pFile)
    {
        if (is_readable($pFile)) {
            return $this->_domDoc->load($pFile);
        }

        throw new Exception("'$pFile' is not readable");
    }

    /**
     * Load an XML string into the DOMDocument.
     *
     * @param string $pString String to load
     * @return mixed
     */
    public function loadFromXmlString($pString)
    {
        return $this->_domDoc->loadXML($pString);
    }

    /**
     * Load an HTML file into the DOMDocument.
     *
     * @param string $pFile File to load
     * @return mixed
     */
    public function loadFromHtmlFile($pFile)
    {
        if (is_readable($pFile)) {
            return $this->_domDoc->loadHTMLFile($pFile);
        }

        throw new Exception("'$pFile' is not readable");
    }

    /**
     * Load an HTML string into the DOMDocument.
     *
     * @param string $pString String to load
     * @return mixed
     */
    public function loadFromHtmlString($pString)
    {
        return $this->_domDoc->loadHTML($pString);
    }

    /**
     * Save DOMDocument to a XML file.
     *
     * @param string $pFile Absolute path
     * @return mixed
     */
    public function saveToXmlFile($pFile)
    {
        if (! is_writable($pFile)) {
            throw new Exception("The file '$pFile' is not writable");
        }

        return $this->_domDoc->save($pFile);
    }

    /**
     * Save DOMDocument to a XML string.
     *
     * @return string
     */
    public function saveToXmlString()
    {
        return $this->_domDoc->saveXML();
    }

    /**
     * Save DOMDocument to a HTML file.
     *
     * @param string $pFile Absolute path
     * @return mixed
     */
    public function saveToHtmlFile($pFile)
    {
        if (! is_writable($pFile)) {
            throw new Exception("The file '$pFile' is not writable");
        }

        return $this->_domDoc->saveHTMLFile($pFile);
    }

    /**
     * Save DOMDocument to a HTML string.
     *
     * @return string
     */
    public function saveToHtmlString()
    {
        return $this->_domDoc->saveHTML();
    }

    /**
     * Execute an XPath query.
     *
     * @param string $pQuery XPath query
     * @return mixed
     */
    public function xPathQuery($pQuery, $pNode = NULL)
    {
        $queryResult = $this->_getXPath()->query($pQuery, $pNode);
        if ($queryResult === false) {
            throw new Exception("Invalid XPath request");
        }

        return $queryResult;
    }

    /**
     * Return the values of an XPath query result as a multidimensional array.
     *
     * @param DOMNodeList $pNodeList
     * @return array
     */
    public function nodeListToArray(DOMNodeList $pNodeList)
    {
        $values = array();

        if ($pNodeList === false or ! $pNodeList->length) {
            return $values;
        }

        foreach ($pNodeList as $node) {
            if ($node instanceof DOMElement) {
                if ($node->childNodes and $node->childNodes->length > 1 or ($node->childNodes and $node->childNodes->length == 1 and ! $node->childNodes->item(0) instanceof DOMText)) {
                    $attributes = $this->_getNodeAttributes($node);

                    if (! array_key_exists($node->nodeName, $values)) {
                        if (empty($attributes)) {
                            $values[$node->nodeName] = $this->nodeListToArray($node->childNodes);
                        } else {
                            $values[$node->nodeName] = array_merge($this->nodeListToArray($node->childNodes), $attributes);
                        }
                    } else {
                        if (! is_array($values[$node->nodeName]) or ! array_key_exists(0, $values[$node->nodeName])) {
                            $values[$node->nodeName] = array($values[$node->nodeName]);
                        }

                        if (empty($attributes)) {
                            $values[$node->nodeName][] = $this->nodeListToArray($node->childNodes);
                        } else {
                            $values[$node->nodeName][] = array_merge($this->nodeListToArray($node->childNodes), $attributes);
                        }
                    }
                } else {
                    $attributes = $this->_getNodeAttributes($node);
                    $query = $this->xPathQuery('.//' . $node->nodeName, $node->parentNode);

                    if ($query->length > 1) {
                        if (isset($values[$node->nodeName]) and ! array_key_exists(0, $values[$node->nodeName])) {
                            $values[$node->nodeName] = array($values[$node->nodeName]);
                        }
                        if (empty($attributes)) {
                            $values[$node->nodeName][] = $node->nodeValue;
                        } else {
                            if ($node->nodeValue) {
                                $values[$node->nodeName][] = array_merge(array($node->nodeValue), $attributes);
                            } else {
                                if (isset($values[$node->nodeName]) and is_array($values[$node->nodeName])) {
                                    $values[$node->nodeName][] = $attributes;
                                } else {
                                    $values[$node->nodeName] = $attributes;
                                }
                            }
                        }
                    } else {
                        if (empty($attributes)) {
                            $values[$node->nodeName] = $node->nodeValue;
                        } else {
                            $values[$node->nodeName] = ($node->nodeValue) ? array_merge(array($node->nodeValue), $attributes) : $attributes;
                        }
                    }
                }
            } else if ($node instanceof DOMAttr) {
                if (! array_key_exists($node->name, $values)) {
                    $values[$node->name] = $node->value;
                } else {
                    if (! is_array($values[$node->name])) {
                        $values[$node->name] = array($values[$node->name]);
                    }

                    if (is_array($values[$node->name])) {
                        $values[$node->name][] = $node->value;
                    } else {
                        $values[$node->name] = array($node->value);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Get the node attributes as array.
     *
     * @param DOMElement $pNode
     * @return array
     */
    private function _getNodeAttributes(DOMElement $pNode)
    {
        $attributes = array();
        if ($pNode->attributes->length) {
            foreach ($pNode->attributes as $key => $attr) {
                $attributes[$key] = $attr->value;
            }
        }

        return $attributes;
    }

    /**
     * Return the DOMDocument object.
     *
     * @return DOMDocument
     */
    public function doc()
    {
        return $this->_domDoc;
    }
}
