<?php
namespace Agl\Core\Data;

use \Agl\Core\Agl;

/**
 * Generic methods to manipulate files.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class File
{
	public static function getSubPath($pFileName, $pDepth = 3)
    {
    	Agl::validateParams(array(
			'Int' => $pDepth
        ));

        if (empty($pFileName)) {
            return DS;
        }

        $levels = array();

        for ($i = 0; $i < $pDepth; $i++) {
            $levels[] = substr($pFileName, $i, 1);
        }

        return DS . implode(DS, $levels) . DS;
    }

    /**
     * Create an empty file.
     *
     * @param string $pPath Absolute path to the file to create
     * @return bool
     */
    public static function createEmptyFile($pPath)
    {
        if (! file_exists($pPath)) {
            if (! fopen($pPath, 'w') or ! chmod($pPath, 0777)) {
                return false;
            }
        }

        return true;
    }
}
