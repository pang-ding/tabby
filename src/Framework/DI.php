<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework;

use Consts\DiConsts;
use Tabby\Error\ErrorSys;

class DI implements \Psr\Container\ContainerInterface, \ArrayAccess
{
    private static $_ins = null;

    private $_entries;
    private $_closures;

    private function __construct()
    {
        $this->_entries  = [];
        $this->_closures = [];
    }

    private function __clone()
    {
    }

    /**
     * Get Mysql
     *
     * @return \Tabby\Store\Mysql\DB
     */
    public static function DB()
    {
        return self::_get(DiConsts::DI_MYSQL);
    }

    /**
     * Get Redis
     *
     * @return \Tabby\Store\Redis\Redis
     */
    public static function Redis()
    {
        return self::_get(DiConsts::DI_REDIS);
    }

    /**
     * Get Mongo
     *
     * @return \MongoDB\Database
     */
    public static function Mongo()
    {
        return self::_get(DiConsts::DI_MONGO);
    }

    /**
     * Get Cache
     *
     * @return \Tabby\Middleware\Cache\CacheAbstract
     */
    public static function Cache()
    {
        return self::_get(DiConsts::DI_CACHE);
    }

    /**
     * Get Queue
     *
     * @return \Tabby\Middleware\Queue\QueueAbstract
     */
    public static function Queue()
    {
        return self::_get(DiConsts::DI_QUEUE);
    }

    /**
     * Get Session
     *
     * @return \Tabby\Middleware\Session\SessionAbstract
     */
    public static function Session()
    {
        return self::_get(DiConsts::DI_SESSION);
    }

    private static function _get(string $key)
    {
        if (self::$_ins === null) {
            throw new ErrorSys("DI Error: DI has not been initialized (Get '{$key}').");
        }

        return self::$_ins->get($key);
    }

    /**
     * 单例
     *
     * @return DI
     */
    public static function getIns(): DI
    {
        if (self::$_ins === null) {
            self::$_ins = new self();
        }

        return self::$_ins;
    }

    /**
     * 设置对象, 实例或可以返回实例的闭包
     *
     * @param string         $key
     * @param \Closure|mixed $entry
     */
    public function set(string $key, $entry): void
    {
        if (isset($this->_entries[$key]) || isset($this->_closures[$key])) {
            throw new ErrorSys("DI Set Error: Can't reset the '{$key}'");
        }
        if (empty($entry)) {
            throw new ErrorSys("DI Set Error: Entry cannot be empty '{$key}'");
        }
        if ($entry instanceof \Closure) {
            $this->_closures[$key] = $entry;
        } else {
            $this->_entries[$key] = $entry;
        }
    }

    /**
     * 获取对象
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->_entries[$key])) {
            if (!isset($this->_closures[$key])) {
                throw new ErrorSys("DI Get Error: Entry '{$key}' has not been set.");
            }
            $this->_entries[$key] = $this->_closures[$key]();
            if (empty($this->_entries[$key])) {
                throw new ErrorSys("DI Get Error: Closure '{$key}' returns empty value.");
            }
        }

        return $this->_entries[$key];
    }

    /**
     * 检测对象是否存在
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return isset($this->_entries[$key]) || isset($this->_closures[$key]);
    }

    /**
     * 删除对象
     *
     * @param string $key
     *
     * @return
     */
    public function del($key): void
    {
        if (isset($this->_entries[$key])) {
            $this->_entries[$key] = null;
            unset($this->_entries[$key]);
        }
        unset($this->_closures[$key]);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetUnset($offset): void
    {
        $this->del($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
