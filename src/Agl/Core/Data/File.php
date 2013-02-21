<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate files.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class File
{
    /**
     * Create a path to store files in multiple subdirectories, based on first
     * letters of filename.
     *
     * @param string $pFIleName
     * @param int $pDepth Number of subdirectories
     * @return string
     */
	public static function getSubPath($pFileName, $pDepth = 3)
    {
        if (empty($pFileName)) {
            return DS;
        }

        $levels = array();

        for ($i = 0; $i < $pDepth; $i++) {
            $levels[] = substr($pFileName, $i, 1);
        }

        return implode(DS, $levels) . DS;
    }

    /**
     * Create an empty file.
     *
     * @param string $pPath Absolute path to the file to create
     * @return bool
     */
    public static function createEmpty($pPath)
    {
        if (! file_exists($pPath)) {
            if (! fopen($pPath, 'w') or ! chmod($pPath, 0777)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete a file.
     *
     * @param string $pPath Absolute path to the file to delete
     * @return bool
     */
    public static function delete($pPath)
    {
        if (! is_writable($pPath) or ! unlink($pPath)) {
            return false;
        }

        return true;
    }
}
