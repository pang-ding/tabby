<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Mod\Traits;

use Tabby\Error\ErrorData;
use Tabby\Type\Enums\EnableDict;

trait Enable
{
    public static $ENABLE_KEY = 'enable';

    /**
     * 启用
     *
     * @param int|string $id
     *
     * @return mixed
     */
    public static function on($id, string $key = '')
    {
        if ($key === '') {
            $key = static::$ENABLE_KEY;
        }

        return self::updateById($id, [$key => EnableDict::$ENABLE]);
    }

    /**
     * 禁用
     *
     * @param int|string $id
     *
     * @return mixed
     */
    public static function off($id, string $key = '')
    {
        if ($key === '') {
            $key = static::$ENABLE_KEY;
        }

        return self::updateById($id, [$key => EnableDict::$DISABLE]);
    }

    /**
     * 设置
     *
     * @param int|string $id
     *
     * @return mixed
     */
    public static function setEnable($id, $enable, string $key = '')
    {
        if (!EnableDict::exist($enable)) {
            throw new ErrorData("Mod Error: Set enable of '" . static::getTableName() . "' with value: '{$enable}'");
        }
        if ($key === '') {
            $key = static::$ENABLE_KEY;
        }

        return self::updateById($id, [$key => $enable]);
    }
}
