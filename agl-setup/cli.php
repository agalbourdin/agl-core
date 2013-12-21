<?php
$currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

return array(

    // Copy configuration files.
    'file:copy' => array(
        array(
            $currentDir . 'app/etc/config/main.php',
            APP_PATH    . 'app/etc/config/main.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/acl.php',
            APP_PATH    . 'app/etc/config/core/acl.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/events.php',
            APP_PATH    . 'app/etc/config/core/events.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/layout.php',
            APP_PATH    . 'app/etc/config/core/layout.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/samples/acl.sample.php',
            APP_PATH    . 'app/etc/config/core/samples/acl.sample.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/samples/events.sample.php',
            APP_PATH    . 'app/etc/config/core/samples/events.sample.php'
        ),
        array(
            $currentDir . 'app/etc/config/core/samples/layout.sample.php',
            APP_PATH    . 'app/etc/config/core/samples/layout.sample.php'
        )
    )
);
