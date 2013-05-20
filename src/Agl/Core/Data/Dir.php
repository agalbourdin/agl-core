<?php
namespace Agl\Core\Data;

use \RecursiveDirectoryIterator,
    \RecursiveIteratorIterator,
    \RecursiveRegexIterator,
    \RegexIterator;

/**
 * Generic methods to manipulate directories.
 *
 * @category Agl_Core
 * @package Agl_Core_Data
 * @version 0.1.0
 */

class Dir
{
    /**
     * Recursive deletion of a directory.
     *
     * @param string $pDir
     * @return bool
     */
    public static function deleteRecursive($pDir)
    {
        $files = glob($pDir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                self::deleteRecursive($file);
            } else {
                unlink($file);
            }
        }

        if (is_dir($pDir)) {
            rmdir($pDir);
        }

        return true;
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
        if ($content === false) {
            return array();
        }

        $content = array_map(function($el) use ($pDir) {
            return str_replace($pDir, '', $el);
        }, $content);

        return $content;
    }

    /**
     * Return a list of files matching a regex pattern in a directory
     * (recursive).
     *
     * @param string $pDir The directory to scan
     * @param string $pRegex Regex for filename
     * @return array Files with absolute path
     */
    public static function listFilesRecursive($pDir, $pRegex = '(.*)')
    {
        $content   = array();
        $Directory = new RecursiveDirectoryIterator($pDir);
        $Iterator  = new RecursiveIteratorIterator($Directory);
        $Regex     = new RegexIterator($Iterator, '/^(.*)' . $pRegex . '$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($Regex as $file) {
            $content[] = $file[0];
        }

        return $content;
    }

    /**
     * Create a directory (recursively).
     *
     * @param string $pDir Absolute path to the directory to create
     * @return bool
     */
    public static function create($pDir)
    {
        if (! is_dir($pDir) and ! mkdir($pDir, 0777, true)) {
            return false;
        }

        return true;
    }
}
