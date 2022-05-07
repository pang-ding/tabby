<?php

//ini_set('display_errors','On');
define('APP_NAME', basename(__DIR__));
define('HOME_PATH', dirname(dirname(__DIR__)) . '/');

\Yaf\Loader::import(HOME_PATH . 'vendor/autoload.php');
(new \Yaf\Application(HOME_PATH . 'conf/yaf_console.ini', ini_get('yaf.environ')))
    ->bootstrap()
    ->run();
