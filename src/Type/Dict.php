<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Type;

abstract class Dict
{
    /**
     * @var array
     */
    const DICT = [];

    /**
     * 取值
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get(string $key)
    {
        return static::DICT[$key] ?? null;
    }

    /**
     * 返回所有键名
     *
     * @return array
     */
    public static function keys(): array
    {
        return array_keys(static::DICT);
    }

    /**
     * 键是否存在
     *
     * @param string $key
     *
     * @return bool
     */
    public static function keyExist($key): bool
    {
        return static::exist($key);
    }

    /**
     * 键是否存在
     *
     * @param string $key
     *
     * @return bool
     */
    public static function exist($key): bool
    {
        return array_key_exists($key, static::DICT);
    }

    /**
     * 搜索给定的值, 返回首个相应的键名
     * 成功返回键名, 失败返回 false
     *
     * @param mixed $value
     * @param bool  $strict 是否严格比较 ===
     *
     * @return mixed string|int|false
     */
    public static function getKey($value, bool $strict = false)
    {
        return array_search($value, static::DICT, $strict);
    }

    /**
     * 搜索给定的值, 返回全部相应的键名
     *
     * @param mixed $value
     * @param bool  $strict 是否严格比较 ===
     *
     * @return array
     */
    public static function getKeysByValue($value, bool $strict = false)
    {
        return array_keys(static::DICT, $value, $strict);
    }
}
