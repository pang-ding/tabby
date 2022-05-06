<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Validator;

class Formator
{
    public static function str(array &$args)
    {
        if (($args['flag'] & Validator::FLAG_OFF_FORMAT) === Validator::FLAG_OFF_FORMAT) {
            return true;
        }
        if (($args['flag'] & Validator::FLAG_OFF_XSS) === 0 && $args['value'] !== '') {
            // 不需要 ENT_SUBSTITUTE 有x80...以上的东西报错比较安全
            $args['value'] = htmlspecialchars(htmlspecialchars_decode($args['value'], ENT_QUOTES), ENT_QUOTES, 'UTF-8');
            if ($args['value'] === '') {
                return ['str_broken', null];
            }
        }

        return true;
    }

    public static function int(array &$args)
    {
        return true;
    }

    public static function float(array &$args)
    {
        // 验证步骤如果改动 value, 则必须保证输出内容的类型与格式正确
        // 所以这里不需要再做格式化
        // if (filter_var($args['value'], FILTER_VALIDATE_FLOAT)) { // float 或 可以转float
        //     $args['value'] = empty($args['typeArgs']) ? (float)$args['value'] : round($args['value'], (int)$args['typeArgs']);
        // }

        return true;
    }

    public static function datetime(array &$args)
    {
        if ($args['value'] instanceof \DateTime) { // 可能是 TabbyConsts::DATETIME_EMPTY_VALUE
            $args['value'] = $args['value']->format($args['typeArgs']);
        }

        return true;
    }

    public static function other(array &$args)
    {
        return true;
    }
}
