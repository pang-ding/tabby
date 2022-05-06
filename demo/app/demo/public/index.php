<?php

ini_set('display_errors', 1);
define('APP_NAME', basename(dirname(__DIR__)));
define('HOME_PATH', dirname(dirname(dirname(__DIR__))) . '/');

ob_start();

\Yaf\Loader::import(HOME_PATH . 'vendor/autoload.php');
(new \Yaf\Application(HOME_PATH . 'conf/app_demo.ini', ini_get('yaf.environ')))
    ->bootstrap()
    ->run();
