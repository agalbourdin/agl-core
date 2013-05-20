<?php
namespace Agl\Core;

use \Composer\Script\PackageEvent,
    \Exception,
    \RecursiveDirectoryIterator,
    \RecursiveIteratorIterator,
    \RecursiveRegexIterator,
    \RegexIterator;

/**
 * Composer Observer.
 * Provide automatic installation of packages config files.
 *
 * @category Agl_Core
 * @package Agl_Core
 * @version 0.1.0
 */

class Composer
{
    /**
     * Package setup directory.
     */
    const SETUP_DIR = 'agl-setup';

    /**
     * Application's etc directory.
     * Config files will be copied in this directory.
     */
    const DESTINATION_DIR = 'app/etc';

    /**
     * Post package install event, fired by Composer.
     * Create config directories and files in application if required by
     * package.
     *
     * @param PackageEvent $pEvent
     * @return bool
     */
    public static function postPackageInstall(PackageEvent $pEvent)
    {
        $path       = realpath('.'
                    . DIRECTORY_SEPARATOR
                    . $pEvent->getComposer()->getConfig()->get('vendor-dir')
                    . DIRECTORY_SEPARATOR
                    . $pEvent->getOperation()->getPackage()->getName()
                    . DIRECTORY_SEPARATOR
                    . self::SETUP_DIR
        ) . DIRECTORY_SEPARATOR;

        if ($path !== DIRECTORY_SEPARATOR and is_readable($path)) {
            $Directory = new RecursiveDirectoryIterator($path);
            $Iterator  = new RecursiveIteratorIterator($Directory);
            $Regex     = new RegexIterator($Iterator, '#^(.*)?' . self::SETUP_DIR . '([a-zA-Z0-9\./_-]+\.[a-z]+)$#i', RecursiveRegexIterator::GET_MATCH);
            foreach ($Regex as $file) {
                $fileInfo = pathinfo($file[2]);

                $destination = realpath('.' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::DESTINATION_DIR . DIRECTORY_SEPARATOR . $fileInfo['dirname'] . DIRECTORY_SEPARATOR;
                $destinationFile = $destination . $fileInfo['basename'];

                if (is_readable($destinationFile)) {
                    continue;
                }

                if ((is_dir($destination) and ! is_writable($destination))
                    or (! is_dir($destination) and ! mkdir($destination, 0777, true))
                    or ! copy($path . $file[2], $destinationFile)) {
                    throw new Exception("Installation failed. Check that 'app/etc/' has write permissions (recursively) and that 'agl-core' package is installed.");
                }

                chmod($destinationFile, 0777);
                chmod($destination, 0777);
            }
        }

        return true;
    }
}
