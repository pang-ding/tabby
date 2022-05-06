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

use Consts\TabbyConsts;
use Tabby\Error\ErrorSys;
use Tabby\Utils\Validate;

/**
 * (!!验证步骤如果改动 value, 则必须保证输出内容的类型与格式正确!!)
 *
 * type方法参数:
 * $args['value'] 要检查的变量
 * $args['flag'] 验证附加参数 int|str  FLAG_EMPTY|FLAG_OFF_XSS|FLAG_OFF_FORMAT = 8 = 'ept.noxss.nofmt'
 * $args['typeArgs'] 当前type检查所需参数
 * $args['default'] 默认值 (当默认值不等于null时 参数 FLAG_EMPTY 可以不用设置)
 *
 * 验证方法参数: (!!验证步骤如果改动 value, 则必须保证输出内容的类型与格式正确!!)
 * $args['value'] (!!验证步骤如果改动 value, 则必须保证输出内容的类型与格式正确!!)
 * $args['assertArgs'] 当前检查所需参数
 * $args['flag']
 * $args['typeArgs']
 * $args['default']
 *
 * 方法返回:
 * true: 当前检查通过, 继续进行后续检查
 * false: 无需进行后续检查, 直接返回结果
 * array|null: 当前检查不通过, 返回用以生成错误信息的参数数组
 */
class Executer
{
    const ARGS_SEPARATOR = ',';

    public static $_asserts = [];

    public static function __callStatic($name, $arguments)
    {
        if (!isset(self::$_asserts[$name])) {
            throw new ErrorSys('Validator Error: unknown assert: "' . $name . '"');
        }

        return self::$_asserts[$name](...$arguments);
    }

    public static function str(array &$args)
    {
        if ($args['value'] === null) {
            $args['value'] = '';
        }
        if (is_string($args['value'])) {
            if (($args['flag'] & Validator::FLAG_OFF_TRIM) === 0) {
                $args['value'] = trim($args['value']);
            }
            if ($args['value'] === '') {
                if ($args['default'] !== null) { // 有默认值 => 用默认值 不做后续检查
                    $args['value'] = $args['default'];

                    return false;
                }
                // 没有FLAG_EMPTY标记时 返回 true 继续后续检查
                // 否则 返回 false 不做后续检查
                return ($args['flag'] & Validator::FLAG_EMPTY) === 0;
            }

            return true;
        } elseif (is_scalar($args['value'])) { // 标量 (string 已处理) => 强转string 继续后续检查
            if (empty($args['value'])) {           // 为空场景 '0' 和 空数组 已排除
                if ($args['default'] !== null) {       // 有默认值 => 用默认值 不做后续检查
                    $args['value'] = $args['default'];

                    return false;
                }
                $args['value'] = ''; // 处理 0, false, 0.0 空变量

                return ($args['flag'] & Validator::FLAG_EMPTY) === 0;
            }

            $args['value'] = (string) $args['value']; // 非空标量 => 强转string 继续后续检查

            return true;
        }

        return null; //非标量 (null 已排除) => 检验失败
    }

    public static function int(array &$args)
    {
        if (empty($args['value'])) { // 为空场景 包含 '0'
            if ($args['value'] === []) { // 空数组 => 检验失败
                return null;
            }
            if ($args['default'] !== null) { // 有默认值 => 用默认值 不做后续检查
                $args['value'] = (int) $args['default'];

                return false;
            }
            $args['value'] = 0;

            // 没有FLAG_EMPTY标记时 返回 true 继续后续检查
            // 否则 返回 false 不做后续检查
            return ($args['flag'] & Validator::FLAG_EMPTY) === 0;
        } elseif (is_int($args['value'])) { // int => 继续后续检查
            return true;
        } elseif (filter_var($args['value'], FILTER_VALIDATE_INT)) { // 可以转int
            $args['value'] = (int) $args['value'];

            return true;
        }

        return null; // 检验失败
    }

    public static function float(array &$args)
    {
        if (empty($args['value'])) { // 为空场景 包含 '0'
            if ($args['value'] === []) { // 空数组 => 检验失败
                return null;
            }
            if ($args['default'] !== null) { // 有默认值 => 用默认值 不做后续检查
                $args['value'] = empty($args['typeArgs']) ? (float) $args['default'] : round($args['default'], (int) $args['typeArgs']);

                return false;
            }
            $args['value'] = 0;

            // 没有FLAG_EMPTY标记时 返回 true 继续后续检查
            // 否则 返回 false 不做后续检查
            return ($args['flag'] & Validator::FLAG_EMPTY) === 0;
        } elseif (filter_var($args['value'], FILTER_VALIDATE_FLOAT)) { // float 或 可以转float
            $args['value'] = empty($args['typeArgs']) ? (float) $args['value'] : round($args['value'], (int) $args['typeArgs']);

            return true;
        }

        return null; // 检验失败
    }

    public static function datetime(array &$args)
    {
        // 逻辑:
        // 默认值只能是日期格式, 空字符串等同于没设置
        // 值为空情况
        //      有默认值        => value:默认值 & return:false
        //      没有默认值
        //          允许为空    => value:'' & return:false
        //          不许为空    => value:不变 & return:['example' => ]
        // 有值情况
        //      格式化成功      => value:DateTime & return:true
        //      格式化失败      => value:不变 & return:['example' => ]
        if (empty($args['typeArgs'])) {
            throw new ErrorSys('Validator Error: Format of datetime must be set');
        }
        $args['typeArgs'] = trim($args['typeArgs']);
        if (empty($args['value'])) {    // 为空场景 包含 '0' 没有合适的方式对 '0' 做特殊处理
            if (!empty($args['default'])) { // 有默认值 => 用默认值 不做后续检查 (用empty判断)
                $v = \DateTime::createFromFormat($args['typeArgs'], $args['default']);
                if (empty($v)) {
                    throw new ErrorSys("Validator Error: Format default value failed.(default='{$args['default']}' format='{$args['typeArgs']}')");
                }
                $args['value'] = $v;

                return false;
            } elseif (($args['flag'] & Validator::FLAG_EMPTY) === Validator::FLAG_EMPTY) {
                // 允许为空时 跳过后续检查 返回 TabbyConsts::DATETIME_VALUE_EMPTY, format阶段参照处理
                $args['value'] = TabbyConsts::DATETIME_VALUE_EMPTY;

                return false;
            }
            // 不允许为空 返回错误参数
            substr($args['typeArgs'], -1) === '+' && $args['typeArgs'] = substr($args['typeArgs'], 0, -1); // 去掉format加号

            return ['example' => date($args['typeArgs'])];
        }
        $v                                                         = \DateTime::createFromFormat($args['typeArgs'], $args['value']);
        substr($args['typeArgs'], -1) === '+' && $args['typeArgs'] = substr($args['typeArgs'], 0, -1); // 去掉format加号

        // todo: 用 array_pop($err['warnings']) 的方式检查有点坑, 判断 'Trailing data' 也不保险. 待改
        $err = date_get_last_errors();
        if (empty($v) || ($err['warning_count'] > 0 && array_pop($err['warnings']) !== 'Trailing data')) { // 格式化失败
            return ['example' => date($args['typeArgs'])];
        }
        $args['value'] = $v;

        return true;
    }

    public static function other(array &$args)
    {
        if (empty($args['value']) && $args['default'] !== null) {
            $args['value'] = $args['default'];
        }

        return true;
    }

    public static function str_min(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_min must be set');
        }

        return mb_strlen($args['value']) < (int) $args['assertArgs'] ? ['min' => (int) $args['assertArgs']] : true;
    }

    public static function str_max(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_max must be set');
        }

        return mb_strlen($args['value']) > (int) $args['assertArgs'] ? ['max' => (int) $args['assertArgs']] : true;
    }

    public static function str_between(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_between must be set');
        }
        list($min, $max) = explode(self::ARGS_SEPARATOR, $args['assertArgs']);
        $len             = mb_strlen($args['value']);

        return ($len < (int) $min || $len > (int) $max) ? ['min' => (int) $min, 'max' => (int) $max] : true;
    }

    public static function str_len(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_len must be set');
        }

        return mb_strlen($args['value']) === (int) $args['assertArgs'] ? true : ['len' => (int) $args['assertArgs']];
    }

    public static function str_in(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_in must be set');
        }
        if (in_array($args['value'], explode('|', $args['assertArgs']), true)) {
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    }

    public static function str_regex(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_regex must be set');
        }
        if (preg_match($args['assertArgs'], $args['value'])) {
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    }

    public static function str_hasid(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_hasid must be set');
        }
        if ($args['assertArgs']::hasId($args['value'])) {
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    }

    public static function str_enum(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_enum must be set');
        }

        return $args['assertArgs']::exist($args['value']) ? true : null;
    }

    public static function str_hasval(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of str_hasval must be set');
        }
        $assertArgs = explode(',', $args['assertArgs']);
        if (count($assertArgs) > 2) {
            $rst = $assertArgs[0]::hasVal($assertArgs[1], $args['value'], $assertArgs[2]);
        } else {
            $rst = $assertArgs[0]::hasVal($assertArgs[1], $args['value']);
        }
        if ($rst) {
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    }

    public static function str_mobile(array &$args)
    {
        if (Validate::isMobile($args['value'])) {
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    }

    public static function str_email(array &$args)
    {
        $args['value'] = strtolower($args['value']);
        if (Validate::isEmail($args['value'])) {
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    }

    public static function int_min(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_min must be set');
        }

        return $args['value'] < (int) $args['assertArgs'] ? ['min' => (int) $args['assertArgs']] : true;
    }

    public static function int_max(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_max must be set');
        }

        return $args['value'] > (int) $args['assertArgs'] ? ['max' => (int) $args['assertArgs']] : true;
    }

    public static function int_between(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_between must be set');
        }
        list($min, $max) = explode(self::ARGS_SEPARATOR, $args['assertArgs']);

        return ($args['value'] < (int) $min || $args['value'] > (int) $max) ? ['min' => (int) $min, 'max' => (int) $max] : true;
    }

    public static function int_in(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_in must be set');
        }

        return in_array($args['value'], explode('|', $args['assertArgs'])) ? true : null;
    }

    public static function int_enum(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_enum must be set');
        }

        return $args['assertArgs']::exist($args['value']) ? true : null;
    }

    public static function int_hasid(array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_hasid must be set');
        }

        return $args['assertArgs']::hasId($args['value']) ? true : null;
    }

    public static function float_min(array &$args)
    {
        return $args['value'] < (float) $args['assertArgs'] ? ['min' => (float) $args['assertArgs']] : true;
    }

    public static function float_max(array &$args)
    {
        return $args['value'] > (float) $args['assertArgs'] ? ['max' => (float) $args['assertArgs']] : true;
    }

    public static function float_between(array &$args)
    {
        list($min, $max) = explode(self::ARGS_SEPARATOR, $args['assertArgs']);
        $min             = (float) $min;
        $max             = (float) $max;

        return ($args['value'] < $min || $args['value'] > $max) ? ['min' => $min, 'max' => $max] : true;
    }

    public static function datetime_min(array &$args)
    {
        $min = \DateTime::createFromFormat($args['typeArgs'], $args['assertArgs']);
        if ($min === false) {
            throw new ErrorSys('Validator Error: datetime_min format failed: "' . $args['assertArgs'] . '"');
        }

        return $args['value']->getTimestamp() < $min->getTimestamp() ? ['min' => $min->format($args['typeArgs'])] : true;
    }

    public static function datetime_max(array &$args)
    {
        $max = \DateTime::createFromFormat($args['typeArgs'], $args['assertArgs']);
        if ($max === false) {
            throw new ErrorSys('Validator Error: datetime_max format failed: "' . $args['assertArgs'] . '"');
        }

        return $args['value']->getTimestamp() > $max->getTimestamp() ? ['max' => $max->format($args['typeArgs'])] : true;
    }

    public static function datetime_between(array &$args)
    {
        list($min, $max) = explode(self::ARGS_SEPARATOR, $args['assertArgs']);
        $min             = \DateTime::createFromFormat($args['typeArgs'], $min);
        $max             = \DateTime::createFromFormat($args['typeArgs'], $max);
        if ($min === false || $max === false) {
            throw new ErrorSys('Validator Error: datetime_between format failed: "' . $args['assertArgs'] . '"');
        }

        return ($args['value']->getTimestamp() < $min->getTimestamp() || $args['value']->getTimestamp() > $max->getTimestamp()) ? ['min' => $min->format($args['typeArgs']), 'max' => $max->format($args['typeArgs'])] : true;
    }
}
