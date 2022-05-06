# Request (___获取输入参数的工具___)

* HttpRequest
* CliRequest (尚未实现)

#### Request实例获取方式

1) 在控制器 Action 方法参数中加入 "$req" 可以得到 Request 实例
2) 通过 Tabby工具 (\T::$REQ) 可以在项目任何位置取得 Request 实例

## HttpRequest

___HttpRequest 依赖验证器. 因此, 使用前需要注册验证规则, 否则将导致异常___

#### HttpRequest 实现了 \ArrayAccess 接口:

```php
// 以下代码等价:
$req['foo']; 
$req->request('foo');
```

#### 取单个参数

```php
$req['foo'];
```

#### 批量取参数, 返回Data对象, ___注意: 返回值 Data 是 \ArrayObject 对象, 不是 array___

```php
$req->data(['foo', 'bar']);
```

#### 变量名可以附带 验证器Flag

```php
$req['foo.ept']; // 允许为空
$req['foo.arr']; // 获取数组参数(foo[]=a&foo[]=b)
$req['foo.notrim']; // 不进行trim过滤
// ...更多Flag及验证规则详情, 请查阅: 验证器
```

#### 批量取参数时, 如果以KV形式传入参数名, value将作为默认值使用

```php
$req->data(['foo', 'bar' => 'bar_defaule_value']); // 当参数bar不存在时返回 'bar_defaule_value'
```

#### 也可以使用以下方法, 指定数据源获取单个参数, 这样做可以临时自定义错误信息

```php
/** 
 * @param string $key       参数名
 * @param mixed  $default   默认值
 * @param string $msg       自定义异常信息
 */
$req->request(string $key, $default = null, string $msg = '');// $_REQUEST
$req->get(string $key, $default = null, string $msg = '');    // $_GET
$req->post(string $key, $default = null, string $msg = '');   // $_POST
```

#### 取Cookie:

```php
// 注意: 当规则验证不通过时, 会返回默认值, 不会抛异常. 这符合大多数使用场景的诉求
$req->cookie(string $key, string $rule = 'str|between:1,1000', string $default = '', $flag = TabbyConsts::VALIDATOR_FLAG_DEFAULT)
```

#### 取分页参数:

```php
// 默认不允许客户端改变每页行数, 通过第三个参数 $sizeKey 可以开启这个功能
/**
 * @param int     $defaultPageSize 每页行数(服务端), 默认值: TabbyConsts::PAGE_SIZE_DEFAULT
 * @param string  $numKey          当前页参数名, 默认值: TabbyConsts::PAGE_NUM_KEY
 * @param ?string $sizeKey         每页行数参数名(客户端), 默认值: null, 可以选择: TabbyConsts::PAGE_SIZE_KEY
 */
$req->getPages(int $defaultPageSize = TabbyConsts::PAGE_SIZE_DEFAULT, string $numKey = TabbyConsts::PAGE_NUM_KEY, ?string $sizeKey = null)
```

#### 检查参数是否存在:

```php
$req->exists(string $key)
```

___更多详情请查阅: 文档 > HttpRequest___