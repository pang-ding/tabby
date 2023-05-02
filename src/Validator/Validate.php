<?php
namespace Tabby\Validator;

use Consts\TabbyConsts;
use Tabby\Error\ErrorAssert;
use Tabby\Error\ErrorInput;
use Tabby\Error\ErrorSys;
use Tabby\Framework\Language;
use Tabby\Framework\Request\HttpRequest;
use Tabby\Validator\Data;
use Tabby\Validator\Rules;

/**
 * 验证器 - 附带格式化
 *
 *
 * FLAG备注:
 *      ept和null: 有默认值时, 都不起作用. 区别: 除去判断方式外, ept将变量转变为类型对应的0值, null则直接返回null类型
 */
class Validate
{
    const FLAG_EMPTY      = Validator::FLAG_EMPTY;
    const FLAG_NULL       = Validator::FLAG_NULL;
    const FLAG_ARRAY      = Validator::FLAG_ARRAY;
    const FLAG_OFF_TRIM   = Validator::FLAG_OFF_TRIM;
    const FLAG_OFF_XSS    = Validator::FLAG_OFF_XSS;
    const FLAG_OFF_FORMAT = Validator::FLAG_OFF_FORMAT;

    /**
     * @var Rules
     */
    protected static $_rules = null;

    /**
     * 初始化 框架启动时调用 (首次执行有效)
     *
     * @param array $rules
     *
     */
    public static function init(array $rules = []): void
    {
        if (static::$_rules === null) {
            static::$_rules = new Rules($rules);
        }
    }

    /**
     * 获取规则
     *
     * @return Rules
     */
    public static function getRules(): Rules
    {
        return static::$_rules;
    }

    /**
     * 设置规则
     *
     * @return
     */
    public static function setRules($key, $rule): void
    {
        static::$_rules[$key] = $rule;
    }

    /**
     * 设置规则 (merge)
     *
     * @param array $rules
     *
     */
    public static function mergeRules(array $rules): void
    {
        static::$_rules->merge($rules);
    }

    /**
     * 设置自定义错误信息 (merge)
     *
     * @param array $customMsg
     *
     */
    public static function mergeCustomMsg(array $customMsg): void
    {
        static::$_rules->mergeCustomMsg($customMsg);
    }

    /**
     * 设置自定义验证方法 (merge)
     *
     * 方法: func(&$args) 只有一个参数(引用)
     * 参数 $args 内容:
     * [
     * 'value'    => &$value,   // 值
     * 'flag'     => &$flag,    // (int) ept|null|notrim... 常量在Rules中
     * 'assertArgs' => $args,   // 验证方法参数 例如: datetime:Y-m-d|min:2018-01-01 中的 '2018-01-01'
     * 'typeArgs' => &$typeArgs,// type附带的参数 例如: datetime:Y-m-d|min:2018-01-01 中的 'Y-m-d'
     * 'default'  => &$default, // 默认值
     * 'type'     => &$type     // 类型名, 即当前方法名, 必须在方法中替换为默认类型(str|int|float|datetime|other), 否则formator过程会报错(自己实现formator除外)
     * ]
     * 返回值:
     *      true:   通过, 继续执行后续检查
     *      false:  通过, 无需执行后续检查
     *      array:  不通过, 输出错误信息, 格式:[断言方法名, 用于格式化消息的参数], 例如: ['int_between', ['min'=>10,'max'=>20]]
     *
     * @param array $assert
     *
     */
    public static function mergeAssert(array $assert): void
    {
        Validator::mergeAssert($assert);
    }

    /**
     * 设置自定义类型 (merge)
     *
     * 方法: func(&$args) 只有一个参数(引用)
     * 参数 $args 内容:
     * [
     * 'value'    => &$value,   // 值
     * 'flag'     => &$flag,    // (int) ept|null|notrim... 常量在Rules中
     * 'typeArgs' => &$typeArgs,// type附带的参数 例如: datetime:Y-m-d|min:2018-01-01 中的 'Y-m-d'
     * 'default'  => &$default, // 默认值
     * 'type'     => &$type     // 类型名, 即当前方法名, 必须在方法中替换为默认类型(str|int|float|datetime|other), 否则formator过程会报错(自己实现formator除外)
     * ]
     * 返回值:
     *      true:   通过, 继续执行后续检查
     *      false:  通过, 无需执行后续检查
     *      array:  不通过, 输出错误信息, 格式:[类型名, 用于格式化消息的参数], 例如: ['datetime', ['example'=>'2020-01-01']]
     *
     * @param array $assert
     *
     */
    public static function mergeTypeAssert(array $assert): void
    {
        Validator::mergeTypeAssert($assert);
    }

    /**
     * 设置 Executer
     *
     * @param string $executer
     *
     */
    public static function setExecuter(string $executer): void
    {
        Validator::setExecuter($executer);
    }

    /**
     * 设置 Formator
     *
     * @param string $formator
     *
     */
    public static function setFormator(string $formator): void
    {
        Validator::setFormator($formator);
    }

    /**
     * 使用 规则串 断言 外部参数 (抛异常, 输出完整报错信息)
     *
     * @param mixed  $value   被检测变量
     * @param string $rule    规则字符串
     * @param string $key     变量名称 主要用于返回异常信息以及记录log 不能带flag
     * @param mixed  $flag    检验规则 int|string|null (等于 null 时使用默认值 TabbyConsts::VALIDATOR_FLAG_DEFAULT)
     * @param mixed  $default 默认值
     * @param string $msg     错误信息 不设置时使用语言包中对应的错误描述(如果存在) 或 默认错误信息
     *
     * @return mixed 处理后的变量 失败抛 ErrorInput 异常
     */
    public static function assertInputValueByRule($value, string $rule, string $key, $flag = null, $default = null, string $msg = '')
    {
        $flag = Rules::normalizeFlag($flag);
        $rst  = Validator::assert($value, $rule, $flag, $default);
        if ($rst === true) {
            return $value;
        }
        $msg = static::$_rules->formatMsg($key, $rst[0], $rst[1], $msg);

        throw new ErrorInput($msg, ['key' => $key], static::formatLog($msg, $rule, $value, $key));
    }

    /**
     * 使用 规则串 断言 系统数据 (抛异常, 隐藏报错信息)
     *
     * @param mixed  $value   被检测变量
     * @param string $rule    规则字符串
     * @param mixed  $flag    检验规则 int|string|null (等于 null 时使用默认值 TabbyConsts::VALIDATOR_FLAG_DEFAULT)
     * @param mixed  $default 默认值
     * @param string $msg     错误信息 只用于记录log
     * @param string $key     变量名称 主要用于返回异常信息以及记录log 不能带flag
     *
     * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
     */
    public static function assertSysValueByRule($value, string $rule, $flag = null, $default = null, string $msg = '', string $key = '')
    {
        $flag = Rules::normalizeFlag($flag);
        $rst  = Validator::assert($value, $rule, $flag, $default);
        if ($rst === true) {
            return $value;
        }

        throw new ErrorAssert(static::formatLog($msg, $rule, $value, $key));
    }

    /**
     * 使用 规则配置 断言 外部参数 (抛异常, 输出完整报错信息)
     *
     * @param mixed  $value   被检测变量
     * @param string $key     Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
     * @param mixed  $default 默认值
     * @param string $msg     错误信息 不设置时使用语言包中对应的错误描述(如果存在) 或 默认错误信息
     *
     * @return mixed 处理后的变量 失败抛 ErrorInput 异常
     */
    public static function assertInputValueByKey($value, string $key, $default = null, string $msg = '')
    {
        list($rule, $flag) = static::getRuleAndFlagByKey($key);

        return static::assertInputValueByRule($value, $rule, $key, $flag, $default, $msg);
    }

    /**
     * 使用 规则配置 断言 系统数据 (抛异常, 输出默认报错信息)
     *
     * @param mixed  $value   被检测变量
     * @param string $key     Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
     * @param mixed  $default 默认值
     *
     * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
     */
    public static function assertSysValueByKey($value, string $key, $default = null)
    {
        list($rule, $flag) = static::getRuleAndFlagByKey($key);

        return static::assertSysValueByRule($value, $rule, $flag, $default, '', $key);
    }

    /**
     * 使用 规则配置 断言 外部参数 (抛异常, 输出完整报错信息)
     *
     * @param \ArrayAccess $data      数据源
     * @param string       $key       Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
     * @param mixed        $default   默认值
     * @param string       $msg       错误信息 不设置时使用语言包中对应的错误描述(如果存在) 或 默认错误信息
     * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
     *
     * @return mixed 处理后的变量 失败抛 ErrorInput 异常
     */
    public static function assertInputValue($data, string $key, $default = null, string $msg = '', bool $trustData = true)
    {
        list($rule, $flag) = static::getRuleAndFlagByKey($key);
        if (isset($data[$key])) {
            if ($trustData && $data instanceof Data) { // Data 特殊处理 不做检查, 直接赋值返回
                return $data[$key];
            } else {
                $value = $data[$key];
            }
        } else {
            $value = null;
        }

        return static::assertInputValueByRule($value, $rule, $key, $flag, $default, $msg);
    }

    /**
     * 使用 规则配置 断言 系统数据 (抛异常, 输出默认报错信息)
     *
     * @param \ArrayAccess $data      数据源
     * @param string       $key       Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
     * @param mixed        $default   默认值
     * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
     *
     * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
     */
    public static function assertSysValue($data, string $key, $default = null, bool $trustData = true)
    {
        list($rule, $flag) = static::getRuleAndFlagByKey($key);

        if (isset($data[$key])) {
            if ($trustData && $data instanceof Data) { // Data 特殊处理 不做检查, 直接赋值返回
                return $data[$key];
            } else {
                $value = $data[$key];
            }
        } else {
            $value = null;
        }

        return static::assertSysValueByRule($value, $rule, $flag, $default, '', $key);
    }

    /**
     * 使用 规则配置 批量 断言 外部参数 (抛异常, 输出完整报错信息)
     *
     * @param \ArrayAccess $data      数据源
     * @param array        $ruleKeys  待检测变量(在 $data 中的) key 数组
     * @param bool         $assertAll 是否全量检测 默认发生错误中断检测. 设置为 true 时, 会对所有数据进行检测, 并返回全部错误信息
     * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
     *
     * @return Data
     */
    public static function assertInputData($data, array $ruleKeys, bool $assertAll = false, bool $trustData = true): Data
    {
        if ($data instanceof HttpRequest) {
            $data    = $_REQUEST;
            $checked = false;
        } else {
            $checked = $trustData && $data instanceof Data;
        }
        $rst     = new Data();
        $errData = [];
        foreach ($ruleKeys as $k => $v) {
            if (is_int($k)) {
                $key     = $v;
                $default = null;
            } else {
                $key     = $k;
                $default = $v;
            }
            list($rule, $flag) = static::getRuleAndFlagByKey($key);

            if ($checked && isset($data[$key])) { // Data 特殊处理 不做检查, 直接赋值返回
                $rst[$key] = $data[$key];

                continue;
            }

            if ($assertAll) {
                $value = $data[$key] ?? null;
                $r     = Validator::assert($value, $rule, $flag, $default);
                if ($r === true) {
                    $rst[$key] = $value;
                } else {
                    $errData[$key] = static::$_rules->formatMsg($key, $r[0], $r[1], '');
                }
            } else {
                $rst[$key] = static::assertInputValueByRule($data[$key] ?? null, $rule, $key, $flag, $default);
            }
        }
        if ($errData === []) {
            return $rst;
        }

        throw new ErrorInput(
            Language::getMsg(TabbyConsts::LANG_PKG_ASSERT, TabbyConsts::LANG_KEY_ASSERT_ERROR_COUNT, ['count' => count($errData)]),
            $errData,
            'Assert all errmsg: ' . substr(print_r($errData, true), 0, 100)
        );
    }

    /**
     * 使用 规则配置 批量 断言 系统数据 (抛异常, 输出默认报错信息)
     *
     * @param \ArrayAccess $data      数据源
     * @param array        $ruleKeys  待检测变量(在 $data 中的) key 数组
     * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
     *
     * @return Data
     */
    public static function assertSysData($data, array $ruleKeys, bool $trustData = true): Data
    {
        if ($data instanceof HttpRequest) {
            $data    = $_REQUEST;
            $checked = false;
        } else {
            $checked = $trustData && $data instanceof Data;
        }
        $rst = new Data();
        foreach ($ruleKeys as $k => $v) {
            if (is_int($k)) {
                $key     = $v;
                $default = null;
            } else {
                $key     = $k;
                $default = $v;
            }
            list($rule, $flag) = static::getRuleAndFlagByKey($key);

            if ($checked && isset($data[$key])) { // Data 特殊处理
                $rst[$key] = $data[$key];

                continue;
            }

            $rst[$key] = static::assertSysValueByRule($data[$key] ?? null, $rule, $flag, $default, '', $key);
        }

        return $rst;
    }

    /**
     * 使用 规则串 验证数据 (失败返回 false, 成功返回 处理后的数据)
     * 不支持 bool 类型数据验证, bool请直接if
     *
     * @param mixed  $value   被检测变量
     * @param string $rule    规则字符串
     * @param mixed  $flag    检验规则 int|string|null (等于 null 时使用默认值 TabbyConsts::VALIDATOR_FLAG_DEFAULT)
     * @param mixed  $default 默认值
     *
     * @return mixed|false 验证失败返回 false
     */
    public static function checkValueByRule($value, string $rule, $flag = null, $default = null)
    {
        return Validator::assert($value, $rule, Rules::normalizeFlag($flag), $default) === true ? $value : false;
    }

    /**
     * 使用 规则配置 验证数据 (失败返回 false, 成功返回 处理后的数据)
     * 不支持 bool 类型数据验证, bool请直接if
     *
     * @param mixed  $value   被检测变量
     * @param string $rule    规则字符串
     * @param mixed  $default 默认值
     *
     * @return mixed|false 验证失败返回 false
     */
    public static function checkValueByKey($value, string $key, $default = null)
    {
        list($rule, $flag) = static::getRuleAndFlagByKey($key);

        return Validator::assert($value, $rule, $flag, $default) === true ? $value : false;
    }

    /**
     * 使用 规则配置 批量 验证数据 (失败返回 false, 成功返回 处理后的数据)
     *
     * @param \ArrayAccess $data      数据源
     * @param array        $ruleKeys  待检测变量(在 $data 中的) key 数组
     * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
     *
     * @return Data|false 验证失败返回 false
     */
    public static function checkDataByKey(&$data, $ruleKeys, bool $trustData = true)
    {
        if ($data instanceof HttpRequest) {
            $data    = $_REQUEST;
            $checked = false;
        } else {
            $checked = $trustData && $data instanceof Data;
        }
        $rst = new Data();
        foreach ($ruleKeys as $k => $v) {
            if (is_int($k)) {
                $key     = $v;
                $default = null;
            } else {
                $key     = $k;
                $default = $v;
            }
            list($rule, $flag) = static::getRuleAndFlagByKey($key);

            if ($checked && isset($data[$key])) { // Data 特殊处理
                $rst[$key] = $data[$key];

                continue;
            }

            $value = $data[$key] ?? null;
            if (Validator::assert($value, $rule, $flag, $default) !== true) {
                return false;
            }
            $rst[$key] = $value;
        }

        return $rst;
    }

    protected static function formatLog(string $msg, string $rule, $value, string $key = ''): string
    {
        return "Assert Failed: {$msg} [{$rule}] " . (empty($key) ? 'value' : $key) . '=`' . substr(print_r($value, true), 0, 50) . '`';
    }

    protected static function getRuleAndFlagByKey(string &$key): array
    {
        $flag = static::$_rules->stripFlagByRuleStr($key);
        if (!isset(static::$_rules[$key])) {
            throw new ErrorSys("Rules Error: Unknown rule '{$key}'");
        }

        return [static::$_rules[$key], $flag];
    }
}
