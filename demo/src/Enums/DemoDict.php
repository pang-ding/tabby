<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Enums;

use Tabby\Type\Dict;

class ArticleAuthDict extends Dict
{
    public static $PUBLIC = 0;
    public static $USER   = 10;
    public static $ADMIN  = 20;

    const DICT = [
        0  => '公开',
        10 => '登录用户',
        20 => '管理员',
    ];
}
