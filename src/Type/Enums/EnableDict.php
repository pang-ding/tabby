<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Type\Enums;

use Tabby\Type\Dict;

class EnableDict extends Dict
{
    public static $DISABLE       = 0;
    public static $ENABLE        = 1;

    const DICT = [
        0  => '禁用',
        1  => '启用',
    ];
}
