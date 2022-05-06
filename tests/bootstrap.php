<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Consts\DiConsts;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as MonoLogger;
use Tabby\Framework\DI;
use Tabby\Store\Mysql\Conn;
use Tabby\Store\Mysql\DB;
use Tabby\Test\Context;

define('APP_NAME', 'test');
define('HOME_PATH', dirname(__DIR__) . '/tests/project/');
\Yaf\Loader::import(dirname(dirname(__DIR__)) . 'vendor/autoload.php');
Context::$app = new \Yaf\Application(__DIR__ . '/yaf.ini', ini_get('yaf.environ'));

$dispatcher    = \Yaf\Dispatcher::getInstance();
$conf          = $dispatcher->getApplication()->getConfig()->toArray();
Context::$conf = $conf;

// Logger
//  %context% %extra% 不用不记
$formatter = new LineFormatter('[%level_name%] %message%', 'm-d, H:i:s');
$handler   = new SyslogHandler($conf['app']['name'], LOG_LOCAL6);
$handler->setFormatter($formatter);
$logger = new MonoLogger($conf['prj']['name'], [$handler]);

Tabby\Tabby::init($logger, $conf);

// 注册 路由
Tabby\Tabby::$Disp->getRouter()->addRoute('_TabbyRouter', new \Tabby\Framework\Router());

Context::$mysqlConf = [
    'dsn'      => 'mysql:host=' . Context::$conf['mysql']['host'] . ';port=' . Context::$conf['mysql']['port'] . ';dbname=' . Context::$conf['mysql']['dbname'] . '',
    'username' => Context::$conf['mysql']['username'],
    'password' => Context::$conf['mysql']['password'],
];

DI::getIns()->set(DiConsts::DI_MYSQL, new DB(new Conn(
    Context::$mysqlConf['dsn'],
    Context::$mysqlConf['username'],
    Context::$mysqlConf['password'],
    [
        \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT => true,
    ]
)));

DI::getIns()->set(DiConsts::DI_MONGO, function () {
    try {
        $mongoConf = \T::$Conf['mongo'];
        $client    = new \MongoDB\Client($mongoConf['dsn']);
        $db        = $mongoConf['dbname'];

        return $client->$db;
    } catch (Exception $e) {
        throw new ErrorSys('MongoDB 连接失败:' . $e->getMessage());

        return;
    }

    $client = new \MongoDB\Client();
    $db     = $_ENV['mongo']['dbname'];

    return $client->$db;
});
