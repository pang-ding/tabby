<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MyValiExecuter extends \Tabby\Validator\Executer
{
    public static function int_setValueByArgs(&$args)
    {
        $args['value'] = (int) $args['assertArgs'];

        return true;
    }

    public static function int_setValueByTypeArgs(&$args)
    {
        $args['value'] = (int) $args['typeArgs'];

        return true;
    }

    public static function str_returnFalse(&$args)
    {
        return false;
    }

    public static function str_returnTrue(&$args)
    {
        return true;
    }
}
