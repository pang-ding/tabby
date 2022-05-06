# 验证器

___几乎所有验证器功能都可以通过 Facade类 \Tabby\Validator\Validate 使用___

#### 提供 alias:

```php
class_alias(Tabby\Validator\Validate::class, 'Vali');
```

#### 验证方法:

```php
/* 
 * 方法比较多, 解释一下命名规则:
 * 
 * 以 assertInputValueByRule 为例:
 * 分四段: assert Input Value ByRule 
 * 
 * 第一段: assert: 验证失败抛异常  | check: 验证失败返回错误信息
 * 第二段: Input: 显示详细错误信息 | Sys: 显示默认异常信息(例如: 出错了)
 * 第三段: Value: 单独获取一个参数 | Data: 批量获取参数
 * 第四段: ByRule: 直接写规则字串  | ByKey: 使用已经注册的规则
 * 
 * 排列组合后去掉没意义的就是下面这一堆了
 */ 
Vali::assertInputValueByRule();
Vali::assertSysValueByRule();
Vali::assertInputValueByKey();
Vali::assertSysValueByKey();
Vali::assertInputValue();
Vali::assertSysValue();
Vali::assertInputData();
Vali::assertSysData();
Vali::checkValueByRule();
Vali::checkValueByKey();
Vali::checkDataByKey();

// 具体参数看语法提示, 或者 查阅文档
```

#### 规则注册工具类

```php
// 一般情况下, 规则请放在 \src\Rules 目录下
// 通过继承 \Rules\AbstractRules 可以简便的 注册规则 / 自定义规则 / 自定义报错信息
// 当然也可以根据使用场景和习惯自己实现一个 类似AbstractRules 的工具
class CommonRules extends AbstractRules
{
    protected static function data()
    {
        return [
            'page_num'  => 'int|min:0|max:1000',
            'page_size' => 'int|min:0|max:1000',
            'enable'    => 'int|between:0,1',
            'channel'   => [
                'str|ischannel', // ischannel是自定义验证方法
                '频道不存在', // 自定义报错信息
                'str_ischannel' => static function (array &$args) {  // 实现自定义验证方法
                    $channels = \Svc\TagSvc::getChannels();
                    return isset($channels[$args['value']]) ? false : null;
                }
            ],
        ];
    }
}

// 通过调用 (一般在 Controller 中)
CommonRules::uses();
// 或 
CommonRules::uses(['page_num', 'channel']);
// 全部 或 有选择 的注册规则
```


#### 注册规则:

```php
Vali::mergeRules(
    [
        'foo'=> 'str|between:2,10',
        'bar'=> 'int|between:1,100'
    ]
);
```

#### 注册报错信息:

```php
Vali::mergeCustomMsg([
    'foo' => 'foo 写错了',
    'bar_min' => 'bar 太短了',
    'bar_max' => 'bar 太长了',
]);
```

#### 注册验证方法:

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



#### 注册类型: (一般情况下不需要注册类型, 使用other类型即可)

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

#### 自定义 Executer

如项目中有大量自定义验证方法, 且应用面较广时, 可以通过自定义 Executer类 解决问题

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

#### 自定义 Formator

类似于自定义 Executer, 如果对验证器格式化方法有特殊需要, 可以通过自定义 Formator类 解决

```php
// 默认使用 \Tabby\Validator\Formator, 继承并添加自定义验证方法
class MyFormator extends \Tabby\Validator\Formator
{
    // ...
}

// 在 Bootstrap 或 AOP(Plugin) routerStartup 注册:
Vali::setFormator(MyFormator::class);
```