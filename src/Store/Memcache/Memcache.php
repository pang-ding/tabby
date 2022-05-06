<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Store\Memcache;

use ErrorSys;
use Tabby\Tabby;

/**
 * Memcached 客户端封装
 *
 */
class Memcache
{
    const SINGLE_SERVER = 0;
    const MULTI_SERVER  = 1;

    private $_memcache;

    private $_servers;
    private $_options;
    private $_persistent_id;

    /**
     * @param array  $servers       Memcached::addServers([['host', port, weight]])  例如:[['127.0.0.1', 11211, 40],['127.0.0.1', 11211, 60]] 权重可选
     * @param array  $options       Memcached::setOptions() 参照:https://www.php.net/manual/zh/memcached.constants.php
     * @param string $persistent_id 长连ID
     */
    public function __construct(array $servers, array $options, string $persistent_id = '')
    {
        $this->_servers       = $servers;
        $this->_persistent_id = $persistent_id;
        $this->setOptions($options);
    }

    /**
     * 返回 Memcached 实例
     *
     * @return \Memcached
     */
    public function getClient(): \Memcached
    {
        if (!$this->_memcache) {
            $this->_memcache = new \Memcached($this->_persistent_id);
            $this->_memcache->setOptions($this->_options);
            if (count($this->_memcache->getServerList()) === 0) {
                $this->_memcache->addServers($this->_servers);
            }
        }

        return $this->_memcache;
    }

    private function setOptions($options)
    {
        if (!isset($options[\Memcached::OPT_PREFIX_KEY])) {
            $options[\Memcached::OPT_PREFIX_KEY] = Tabby::$Conf->get('prj.name') . '_' . Tabby::$Conf->get('app.name');
        }

        // 仿佛不能设置更多默认值了...

        $this->_options = $options;
    }

    public function __call(string $name, array $args)
    {
        return $this->exec($name, $args);
    }

    private function exec(string $cmd, array $args = [])
    {
        try {
            return $this->getClient()->$cmd(...$args);
        } catch (\Throwable $e) {
            throw new ErrorSys($e->getMessage());
        }
    }

    // TODO, 方法封装有空搞, redis写吐了
}
