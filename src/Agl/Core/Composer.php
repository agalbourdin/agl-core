<?php
namespace Agl\Core;

use \Exception,
    \Composer\Script\PackageEvent;

/**
 * Composer events methods. Provide automatic installation of More packages.
 *
 * @category Agl_Core
 * @package Agl_Core
 * @version 0.1.0
 */

class Composer
{
    /**
     * AGL setup directory.
     */
    const SETUP_DIR = 'agl-setup/';

    /**
     * AGL install file.
     */
    const INSTALL_FILE = 'install.json';

    /**
     * Post package install event, fired by Composer.
     * Create config directories and files in application if required by
     * the package.
     *
     * @param PackageEvent $pEvent
     * @return bool
     */
    public static function postPackageInstall(PackageEvent $pEvent)
    {
        $package    = $pEvent->getOperation()->getPackage()->getName();
        $path       = realpath(
            './'
            . $pEvent->getComposer()->getConfig()->get('vendor-dir')
            . '/' . $package
            . '/' . self::SETUP_DIR
        ) . '/';

        if ($path and is_readable($path . self::INSTALL_FILE)) {
            $installFile    = $path . self::INSTALL_FILE;
            $installContent = json_decode(file_get_contents($installFile));

            foreach ($installContent as $file => $config) {
                $destination = realpath('./') . '/' . $config->dir;
                $destinationFile = $destination . $config->file;

                if (is_readable($destinationFile)) {
                    continue;
                }

                if ((is_dir($destination) and ! is_writable($destination))
                    or (! is_dir($destination) and ! mkdir($destination, 0777, true))
                    or ! copy($path . $file, $destinationFile)) {
                    throw new Exception("Installation failed. Check that 'app/etc/' has write permissions (recursively).");
                }
            }
        }

        return true;
    }
}
