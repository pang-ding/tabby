## 验证器
----
___几乎所有验证器功能都可以通过 Facade类 \Tabby\Validator\Validate 使用___

提供 alias:

```php
class_alias(Tabby\Validator\Validate::class, 'Vali');
```

使用 规则字符串 断言 外部参数 (抛异常, 输出完整报错信息)

```php
/**
 * @param mixed  $value   被检测变量
 * @param string $rule    规则字符串
 * @param string $key     变量名称 主要用于返回异常信息以及记录log 不能带flag
 * @param mixed  $flag    检验规则 int|string|null (等于 null 时使用默认值 TabbyConsts::VALIDATOR_FLAG_DEFAULT)
 * @param mixed  $default 默认值
 * @param string $msg     错误信息 不设置时使用语言包中对应的错误描述(如果存在) 或 默认错误信息
 *
 * @return mixed 处理后的变量 失败抛 ErrorInput 异常
 */
Vali::assertInputValueByRule($value, string $rule, string $key, $flag = null, $default = null, string $msg = '')

$username = 'foo_bar';
Vali::assertInputValueByRule($val, 'str|min:6', 'username');
```

使用 规则字符串 断言 系统数据 (抛异常, 隐藏报错信息)

```php
/**
 * @param mixed  $value   被检测变量
 * @param string $rule    规则字符串
 * @param mixed  $flag    检验规则 int|string|null (等于 null 时使用默认值 TabbyConsts::VALIDATOR_FLAG_DEFAULT)
 * @param mixed  $default 默认值
 * @param string $msg     错误信息 只用于记录log
 * @param string $key     变量名称 主要用于返回异常信息以及记录log 不能带flag
 *
 * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
 */
Vali::assertSysValueByRule($value, string $rule, $flag = null, $default = null, string $msg = '', string $key = '')

$username = 'foo_bar';
Vali::assertSysValueByRule($val, 'str|min:6', 'username');
```

使用 规则配置 断言 外部参数 (抛异常, 输出完整报错信息)

```php
/**
 * @param mixed  $value   被检测变量
 * @param string $key     Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
 * @param mixed  $default 默认值
 * @param string $msg     错误信息 不设置时使用语言包中对应的错误描述(如果存在) 或 默认错误信息
 *
 * @return mixed 处理后的变量 失败抛 ErrorInput 异常
 */
Vali::assertInputValueByKey($value, string $key, $default = null, string $msg = '')

$username = 'foo_bar';
Vali::assertInputValueByKey($val, 'username');
```

使用 规则配置 断言 系统数据 (抛异常, 输出默认报错信息)

```php
/**
 * @param mixed  $value   被检测变量
 * @param string $key     Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
 * @param mixed  $default 默认值
 *
 * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
 */
Vali::assertSysValueByKey($value, string $key, $default = null)

$username = 'foo_bar';
Vali::assertInputValueByKey($val, 'username');
```

使用 规则配置 断言 外部参数 (抛异常, 输出完整报错信息)

```php
/**
 * @param \ArrayAccess $data      数据源
 * @param string       $key       Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
 * @param mixed        $default   默认值
 * @param string       $msg       错误信息 不设置时使用语言包中对应的错误描述(如果存在) 或 默认错误信息
 * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
 *
 * @return mixed 处理后的变量 失败抛 ErrorInput 异常
 */
Vali::assertInputValue($data, string $key, $default = null, string $msg = '', bool $trustData = true)

Vali::assertInputValue($_REQUEST, 'username');
```

使用 规则配置 断言 系统数据 (抛异常, 输出默认报错信息)

```php
/**
 * @param \ArrayAccess $data      数据源
 * @param string       $key       Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
 * @param mixed        $default   默认值
 * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
 *
 * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
 */
Vali::assertSysValue($data, string $key, $default = null, bool $trustData = true)

Vali::assertInputValue($_REQUEST, 'username');
```

使用 规则配置 断言 外部参数 (抛异常, 输出默认报错信息)

```php
/**
 * @param \ArrayAccess $data      数据源
 * @param string       $key       Rules 中的 Key 可以附带 flag参数: foo.ept.noxss
 * @param mixed        $default   默认值
 * @param bool         $trustData 是否信任 Data对象 默认=true 不再对 Data对象 中已存在的数据进行检查(直接返回该下标数据), 不存在以 null值 正常检查
 *
 * @return mixed 处理后的变量 失败抛 ErrorAssert 异常
 */
Vali::assertSysValue($data, string $key, $default = null, bool $trustData = true)

Vali::assertInputValue($_REQUEST, 'username');
```

注册规则:

```php
Vali::mergeRules(
    [
        'foo'=> 'str|between:2,10',
        'bar'=> 'int|between:1,100'
    ]
);
```

注册报错信息:

```php
Vali::mergeCustomMsg([
    'foo' => 'foo 写错了',
    'bar_min' => 'bar 太短了',
    'bar_max' => 'bar 太长了',
]);
```

注册验证方法:

```php
/* 参数 $args:
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
*/
Vali::mergeAssert([
    'str_ip' => function (array &$args)
    {
        $rst = filter_var($args['value'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if ($rst) {
            $args['value'] = $rst;
            $args['flag'] = $args['flag'] | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT;

            return true;
        }

        return null;
    },
    'int_min' => (array &$args)
    {
        if ($args['assertArgs'] === '') {
            throw new ErrorSys('Validator Error: Argument of int_min must be set');
        }

        return $args['value'] < (int) $args['assertArgs'] ? ['min' => (int) $args['assertArgs']] : true;
    }

]);
```



注册类型: (一般情况下不需要注册类型, 使用other类型即可)

```php
/* 参数 $args:
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
*/
Vali::mergeTypeAssert([
    'int' => function (array &$args)
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
]);
```

自定义 Executer

___如项目中有大量自定义验证方法, 且应用面较广时, 可以通过自定义 Executer类 解决问题___

```php
// 默认使用 \Tabby\Validator\Executer, 继承并添加自定义验证方法
class MyExecuter extends \Tabby\Validator\Executer
{
    public static function str_ip(array &$args)
    {
        // ...
    }
}

// 在 Bootstrap 或 AOP(Plugin) routerStartup 注册:
Vali::setExecuter(MyExecuter::class);
```

自定义 Formator

___类似于自定义 Executer, 如果对验证器格式化方法有特殊需要, 可以通过自定义 Formator类 解决__

```php
// 默认使用 \Tabby\Validator\Formator, 继承并添加自定义验证方法
class MyFormator extends \Tabby\Validator\Formator
{
    // ...
}

// 在 Bootstrap 或 AOP(Plugin) routerStartup 注册:
Vali::setFormator(MyFormator::class);
```

