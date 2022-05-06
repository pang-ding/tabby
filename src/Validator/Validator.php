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

/**
 * 验证器
 *
 * !!请勿对字面量或常量进行检测!!
 * 被检测值引用传递
 */
class Validator
{
    // 单竖线用着最舒服, 正则冲突这种小概率场景通过加闭包规则解决
    const RULE_SEPARATOR = '|';
    const ARGS_SEPARATOR = ':';

    // Date types
    const DATE_TYPE_STRING   = 'str';
    const DATE_TYPE_INT      = 'int';
    const DATE_TYPE_FLOAT    = 'float';
    const DATE_TYPE_DATETIME = 'datetime';
    const DATE_TYPE_OTHER    = 'other';

    const FLAG_EMPTY      = 1;
    const FLAG_NULL       = 2;
    const FLAG_ARRAY      = 4;
    const FLAG_OFF_TRIM   = 8;
    const FLAG_OFF_XSS    = 16;
    const FLAG_OFF_FORMAT = 32;

    /**
     * 允许的 Date type
     *
     * @var array
     */
    public static $Types = [
        self::DATE_TYPE_INT,
        self::DATE_TYPE_FLOAT,
        self::DATE_TYPE_STRING,
        self::DATE_TYPE_DATETIME,
        self::DATE_TYPE_OTHER,
    ];

    /**
     * 断言实现
     *
     * @var string
     */
    protected static $_executer = Executer::class;

    /**
     * 格式化实现
     *
     * @var string
     */
    protected static $_formator = Formator::class;

    public static function setExecuter(string $executer): void
    {
        self::$_executer = $executer;
    }

    public static function setFormator(string $format): void
    {
        self::$_formator = $format;
    }

    public static function mergeAssert(array $asserts): void
    {
        foreach ($asserts as $k => $v) {
            static::registAssert($k, $v);
        }
    }

    public static function registAssert(string $name, callable $func): void
    {
        if (isset(self::$_executer::$_asserts[$name])) {
            throw new ErrorSys("Validator Error: Can not reset assert: '{$name}'");
        }
        // 不对自定义函数进行检测
        self::$_executer::$_asserts[$name] = $func;
    }

    public static function mergeTypeAssert(array $typeAsserts): void
    {
        foreach ($typeAsserts as $k => $v) {
            static::registTypeAssert($k, $v);
        }
    }

    public static function registTypeAssert(string $name, callable $func): void
    {
        // 保留用 registAssert 覆盖默认类型验证方法的口子, 特殊场景有用
        if (in_array($name, static::$Types)) {
            throw new ErrorSys("Validator Error: Can not reset type: '{$name}'");
        }

        static::$Types[] = $name;
        static::registAssert($name, $func);
    }

    /**
     * 验证
     *
     * @param mixed  &$value
     * @param string $rule
     * @param int    $flag
     * @param mixed  $default
     *
     * @return array|bool true: 继续执行后续验证 | false: 跳过后续验证 | array: 验证失败, 错误参数用于报错信息
     */
    public static function assert(&$value, string $rule, int $flag = TabbyConsts::VALIDATOR_FLAG_DEFAULT, $default = null)
    {
        // 数组处理
        if (($flag & self::FLAG_ARRAY) === self::FLAG_ARRAY) {
            $flag -= self::FLAG_ARRAY;
            if ($value === null) {
                $value = [];
            }
            if (!is_array($value)) {
                return ['default', null];
            }
            foreach ($value as &$v) {
                $rst = self::assert($v, $rule, $flag, $default);
                if ($rst !== true) {
                    return $rst;
                }
            }

            return true;
        }

        // 拆分rule串
        $rulesArray = array_filter(explode(self::RULE_SEPARATOR, $rule));
        if (count($rulesArray) === 0) {
            throw new ErrorSys('Validator Error: Rule string cannot be empty');
        }

        // rule首段提取为数据类型
        $type     = explode(self::ARGS_SEPARATOR, array_shift($rulesArray), 2);
        $typeArgs = count($type) === 2 ? $type[1] : '';
        $type     = $type[0];
        if (!in_array($type, self::$Types)) {
            throw new ErrorSys("Validator Error: Unknown type '{$type}' with rule '{$rule}'");
        }

        $args = [
            'value'    => &$value,
            'flag'     => &$flag,
            'typeArgs' => &$typeArgs,
            'default'  => &$default,
            'type'     => &$type,
        ];

        // 存在 FLAG_NULL 标记, $value === null 且没有 $default 时, 不对 $velue(null) 做处理 直接返回
        if (($args['flag'] & Validator::FLAG_NULL) === 0 || $value !== null || $default !== null) {
            // 类型处理
            $rst = static::$_executer::$type($args);

            if (!is_bool($rst)) {
                return [$type, $rst];
            } elseif ($rst === true) {
                // 规则处理
                foreach ($rulesArray as $r) {
                    $rArray             = explode(self::ARGS_SEPARATOR, $r, 2);
                    $assertName         = "{$type}_{$rArray[0]}";
                    $args['assertArgs'] = count($rArray) === 2 ? $rArray[1] : '';
                    $rst                = static::$_executer::$assertName($args);
                    if ($rst === true) {
                        continue;
                    } elseif ($rst === false) {
                        break;
                    }
                    // [断言方法名(int_between), 用于格式化消息的参数(['min'=>10,'max'=>20])]
                    return [$assertName, $rst];
                }
            }

            // 后续格式化
            $rst = self::$_formator::$type($args);

            if ($rst !== true) {
                return $rst;
            }
        }

        return true;
    }
}
