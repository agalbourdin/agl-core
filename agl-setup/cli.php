<?php
$currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

return array(

    // Copy configuration files.
    'file:copy' => array(
        array(
            $currentDir . 'app/etc/config/main.php',
            $appPath    . 'app/etc/config/main.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/acl.php',
            $appPath    . 'app/etc/config/core/acl.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/events.php',
            $appPath    . 'app/etc/config/core/events.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/layout.php',
            $appPath    . 'app/etc/config/core/layout.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/samples/acl.sample.php',
            $appPath    . 'app/etc/config/core/samples/acl.sample.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/samples/events.sample.php',
            $appPath    . 'app/etc/config/core/samples/events.sample.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/samples/layout.sample.php',
            $appPath    . 'app/etc/config/core/samples/layout.sample.php'
        )
    ),

    // CHMOD app/var
    'chmod' => array(
        $appPath . 'app/var/',
        777
    )
);
