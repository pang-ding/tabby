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

/**
 * 枚举
 * 仅为使用方便, 不会实例化对象, 不是实际的数据类型
 */
abstract class Enum
{
    /**
     * @var array
     */
    protected static $_enum = null;

    /**
     * 枚举数组
     *
     * @return array
     */
    public static function getArray(): array
    {
        if (static::$_enum === null) {
            static::$_enum = get_class_vars(static::class);
        }

        return static::$_enum;
    }

    /**
     * 检查值是否存在
     *
     * @param mined $value
     * @param bool  $strict 是否严格检查 ===
     *
     * @return bool
     */
    public static function exist($value, bool $strict = false): bool
    {
        return in_array($value, static::getArray(), $strict);
    }
}
