# Response (___数据输出对象___)

### 实例获取方式:

1) 在控制器 Action 方法参数中加入 "$rsp" 可以得到 Response 实例
2) 通过 Tabby工具 (\T::$RSP) 可以在项目任何位置取得 Response 实例

### 基本运行逻辑:
1) 在请求生命周期前段, 通过 AOP(Plugin) 向 Response 注册 渲染器(Render), 决定默认输出方式(模板 / JSON / JSONP / ECHO / NONE)
2) 接到请求后, 业务逻辑代码(一般在Action中)通过向 Response 赋值, 决定输出数据
3) 每当Action执行完毕后, 调度器(Yaf_Dispatcher) 将触发 Response::render 方法
4) Response 调用注入的 Render 中的 response 方法
5) Render 根据 调度器是否调度完毕(比如还有forward尚未执行) 以及 是否存在异常 决定是否输出如何输出, 具体情况参见 Render


#### 设置默认输出方式:

```php
// 一般在 Bootstrap 或 AOP(Plugin) routerStartup 中通过调用以下方法设置默认输出方式
\T::$RSP->setDefaultRender(\Tabby\Framework\Response::RENDER_TPL);
// 可选的参数有:
\Tabby\Framework\Response::RENDER_TPL  // HTML模板
\Tabby\Framework\Response::RENDER_JSON 
\Tabby\Framework\Response::RENDER_JSONP
\Tabby\Framework\Response::RENDER_ECHO // 输出字符串
\Tabby\Framework\Response::RENDER_NONE // 不输出
```

#### 赋值

HttpRequest 实现了 \ArrayAccess 接口:

```php
// 项目中任何位置都可以取得 Response 对象并对其进行赋值.
$rsp['foo'] = 'bar'; 
\T::$RSP['foo'] = 'bar';
```

#### 改变(确定)输出方式 及 参数

```php
// 一般在 Action 中
// 注意, 如果要改变默认输出方式, 请尽量将语句放置在业务逻辑之前. 否则发生异常时设置语句没有执行仍将以默认输出方式输出错误信息
$rsp->tpl('foo/bar'); // 详情见 模板
$rsp->echo('foo'); // ECHO方式只会输出echo()方法参数中的内容, 其他赋值无效
$rsp->json();
$rsp->jsonp();
$rsp->none();
```

#### 跳转(302)

```php
// redirect 默认情况下会立即执行 Render 输出跳转 Header, 然后 exit. 
// 也可以选择将 $now 设置为 false 等待本次Action执行完毕后, 正常触发 Render (除极特殊情况不建议这么做)
$rsp->redirect(string $url, bool $now = true)
```

#### 获得已注册的输出数据

```php
$rsp->getAll(): array
```

#### 覆盖输出数据

```php
$rsp->reset(array $data): void
```

#### 合并输出数据

```php
$rsp->merge(array $data): void
```

#### 清除所有待输出数据

```php
$rsp->clear(): void
```

___更多详情请查阅: 文档 > Response___