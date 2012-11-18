<?php
namespace Agl\Core\Data;

/**
 * Generic methods to manipulate directories.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Directory
{
    /**
     * Recursive deletion of a directory.
     *
     * @param string $pDir
     */
    public static function delTree($pDir)
    {
        $files = glob($pDir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                self::delTree($file);
            } else {
                unlink($file);
            }
        }

        if (is_dir($pDir)) {
            rmdir($pDir);
        }
    }

    /**
     * Return a list of the sub directories of $pDir.
     *
     * @param string $pDir The directory to scan
     * @return array Sub directories
     */
    public static function listDirs($pDir)
    {
        $content = glob($pDir . '*', GLOB_ONLYDIR);

        if (is_array($content)) {
            return $content;
        }

        return array();
    }

    /**
     * Create a directory (recursively).
     *
     * @param string $pDir Absolute path to the directory to create
     * @return bool
     */
    public static function createDir($pDir)
    {
        if (! is_dir($pDir) and ! mkdir($pDir, 0777, true)) {
            return false;
        }

        return true;
    }
}
