<?php
namespace Agl\Core;

use \Composer\Script\PackageEvent,
    \Exception;

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
     * Package CLI configuration file.
     */
    const CLI_FILE = 'cli.php';

    /**
     * Execute the requested command with passed arguments.
     *
     * @param string $pCmd
     * @param array $pArgs
     * @return array
     */
    private static function _execute($pCmd, array $pArgs)
    {
        $result = array();
        exec('php agl ' . $pCmd . ' ' . implode(' ', $pArgs), $result);
        return $result;
    }

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
        if ($pEvent->getOperation() instanceof \Composer\DependencyResolver\Operation\InstallOperation) {
            $packageName = $pEvent->getOperation()->getPackage()->getName();
        } else if ($pEvent->getOperation() instanceof \Composer\DependencyResolver\Operation\UpdateOperation) {
            $packageName = $pEvent->getOperation()->getInitialPackage()->getName();
        } else {
            throw new Exception("Event is not supported.");
        }

        $appPath = realpath('.') . DIRECTORY_SEPARATOR;

        $path = realpath('.'
              . DIRECTORY_SEPARATOR
              . $pEvent->getComposer()->getConfig()->get('vendor-dir')
              . DIRECTORY_SEPARATOR
              . $packageName
              . DIRECTORY_SEPARATOR
              . self::SETUP_DIR
              . DIRECTORY_SEPARATOR
              . self::CLI_FILE
        );

        if ($path and is_readable($path)) {
            $config = require($path);

            if (is_array($config)) {
                foreach ($config as $cmd => $args) {
                    if (! is_array($args)) {
                        continue;
                    } else if (count($args) == count($args, COUNT_RECURSIVE)) {
                        self::_execute($cmd, $args);
                    } else {
                        foreach ($args as $cmdArgs) {
                            self::_execute($cmd, $cmdArgs);
                        }
                    }
                }
            }
        }

        return true;
    }
}
