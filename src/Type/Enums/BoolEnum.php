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

use Tabby\Type\Enum;

class BoolEnum extends Enum
{
    public static $FALSE = 0;
    public static $TRUE  = 1;

    /**
     * @var array
     */
    protected static $_enum = [0, 1];
}
