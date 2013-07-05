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
     * @param string $pContent Optional file content
     * @return bool
     */
    public static function create($pPath, $pContent = '')
    {
        if (! file_exists($pPath)) {
            if (! $fp = fopen($pPath, 'w') or ! chmod($pPath, 0777)) {
                return false;
            }

            if ($pContent) {
                fwrite($fp, $pContent);
            }

            fclose($fp);
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

    /**
     * Write content to a file.
     *
     * @param string $pFile
     * @param string $pContent
     * @param bool $pAppend Append data to file (erase content before write by
     * default)
     * @return mixed
     */
    public static function write($pFile, $pContent, $pAppend = false)
    {
        if (! is_writable($pFile)) {
            self::create($pFile);
        }

        if ($pAppend) {
            return (file_put_contents($pFile, $pContent, FILE_APPEND | LOCK_EX));
        }

        return (file_put_contents($pFile, $pContent, LOCK_EX));
    }
}
