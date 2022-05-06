<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Store\Redis;

use Tabby\Error\ErrorSys;

class Client
{
    public $phpRedis;

    public function __construct(array $conf)
    {
        /*
         * redis.c (commit 0879770 2022-04-05) 中:
         * redis_connect(INTERNAL_FUNCTION_PARAMETERS, int persistent)
         * connect  persistent=0
         * pconnect persistent=1
         * 因此两个方法不通用
         * redis_connect 收参数: &host, &host_len, &port, &timeout, &persistent_id, &persistent_id_len, &retry_interval, &read_timeout, &context
         * 官方文档里 connect() 有个 reserved 参数, 在 redis_connect 里赋值给了 persistent_id
         * 稍后 if (!persistent) { persistent_id = NULL;} , 所以 reserved 在 connect 中没起作用
         * !!注意: 4.0.0和当前逻辑基本一致, 更早版本中行为有差异
         */
        $this->phpRedis = new \Redis();
        if (!empty($conf['persistent'])) {
            if (!$this->phpRedis->pconnect(
                $conf['host'],
                $conf['port'],
                $conf['connectTimeout'],
                $conf['persistent'],
                $conf['retryInterval'],
                $conf['readTimeout']
            )) {
                throw new ErrorRedisConn('Redis Error: Connection failed');
            }
        } else {
            if (!$this->phpRedis->connect(
                $conf['host'],
                $conf['port'],
                $conf['connectTimeout'],
                null,
                $conf['retryInterval'],
                $conf['readTimeout']
            )) {
                throw new ErrorRedisConn('Redis Error: Connection failed');
            }
        }
        if (!empty($conf['auth'])) {
            if (!$this->phpRedis->auth($conf['auth'])) {
                throw new ErrorSys('Redis Error: Auth failed');
            }
        }
    }
}
