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
use Tabby\Store\Mysql\Conn;
use Tabby\Store\Mysql\DB;

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:\Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    /**
     * 请确保首先初始化Tabby, 否则会出现异常
     *
     * @param \Yaf\Dispatcher $dispatcher
     */
    public function _initTabby(\Yaf\Dispatcher $dispatcher)
    {
        \T::init();

        // 注册 路由
        \T::$Disp->getRouter()->addRoute('_TabbyRouter', new \Tabby\Framework\Router());

        // 注册 Plugin
        \T::$Disp->registerPlugin(new \Plugins\Console());
    }

    /**
     * 初始化项目资源
     *
     * @param \Yaf\Dispatcher $dispatcher
     */
    public function _initResource(\Yaf\Dispatcher $dispatcher)
    {
        \T::$DI[DiConsts::DI_MYSQL] = new DB(
            new Conn(
                'mysql:host=' . \T::getConf('mysql.host') . ';port=' . \T::getConf('mysql.port') . ';dbname=' . \T::getConf('mysql.dbname') . ';charset=utf8mb4;',
                \T::getConf('mysql.username'),
                \T::getConf('mysql.password'),
                [
                    \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_PERSISTENT => true,
                ]
            )
        );

        \T::$DI['mongo'] = function () {
            try {
                $mongoConf = \T::$Conf['mongo'];
                $dsn       = "mongodb://{$mongoConf['host']}:{$mongoConf['port']}";

                $client = new \MongoDB\Client($dsn);
                $db     = $mongoConf['dbname'];

                return $client->$db;
            } catch (Exception $e) {
                throw new ErrorSys('MongoDB 连接失败:' . $e->getMessage());

                return;
            }
        };
    }
}
