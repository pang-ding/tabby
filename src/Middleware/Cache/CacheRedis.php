<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Cache;

class CacheRedis implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var \Tabby\Store\Redis\Redis
     */
    private $_redis;

    public function __construct(\Tabby\Store\Redis\Redis $redis)
    {
        $this->_redis = $redis;
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     */
    public function get($key, $default = null)
    {
        $rst = $this->_redis->get($key);

        return $rst === false ? $default : $rst;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     */
    public function set($key, $value, $ttl = null)
    {
        if ($ttl) {
            return $this->_redis->set($key, $value, ['ex' => $ttl]);
        }

        return $this->_redis->set($key, $value);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return int 删除数量
     *
     */
    public function delete($key)
    {
        return $this->_redis->del($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        return $this->_redis->flushDb(true);
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys)) {
            $tmp = [];
            foreach ($keys as $v) {
                $tmp[] = $v;
            }
            $keys = $tmp;
        }
        $rst = $this->_redis->mGet($keys);

        if (!is_array($rst)) {
            return array_fill_keys($keys, $default);
        }

        return array_combine($keys, array_replace($rst, array_fill_keys(array_keys($rst, false, true), $default)));
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     */
    public function setMultiple($values, $ttl = null)
    {
        if ($ttl === null) {
            return $this->_redis->mSet($values);
        }

        $r = $this->_redis->multi();
        foreach ($values as $k => $v) {
            $r->set($k, $v, ['ex' => $ttl]);
        }

        return $r->exec() ? true : false;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return int 删除数量
     *
     */
    public function deleteMultiple($keys)
    {
        if (!is_array($keys)) {
            $tmp = [];
            foreach ($keys as $v) {
                $tmp[] = $v;
            }
            $keys = $tmp;
        }

        return $this->_redis->getModeIns()->masterExec('del', $keys);
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     */
    public function has($key)
    {
        return $this->_redis->exists($key) > 0;
    }
}
