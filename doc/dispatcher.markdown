# 调度 & Controller

Tabby 使用 Yaf 调度器 ( Yaf_Dispatcher )

Yaf_Dispatcher: <https://www.php.net/manual/zh/class.yaf-dispatcher.php>

## Bootstrap

```php
/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:\Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    /**
     * 请确保首先初始化Tabby, 否则会出现异常
     *
     * @param \Yaf\Dispatcher $dispatcher
     */
    public function _initTabby(\Yaf\Dispatcher $dispatcher)
    {
        // Logger
        $formatter = new LineFormatter('[%level_name%] %message%', 'm-d, H:i:s');
        $handler   = new SyslogHandler(self::$Conf['app']['name'], LOG_LOCAL6);
        $handler->setFormatter($formatter);
        $handlers = [$handler];
        if (self::$isDebug) {
            $handlers[] = new ChromePHPHandler(MonoLogger::INFO, false);
        }

        $logger = MonoLogger(self::$Conf['prj']['name'], $handlers);

        // 初始化Tabby
        \T::init($logger);

        // 注册 路由
        \T::$Disp->getRouter()->addRoute('_TabbyRouter', new \Tabby\Framework\Router());

        // 注册 Plugin
        \T::$Disp->registerPlugin(new \Plugins\DemoPlugins());

        // 注册 Render
        RenderSvc::init($log);

        // 默认输出方式
        \T::$RSP->setDefaultRender(\Tabby\Framework\Response::RENDER_TPL);
    }

    /**
     * 初始化项目资源
     *
     * @param \Yaf\Dispatcher $dispatcher
     */
    public function _initResource(\Yaf\Dispatcher $dispatcher)
    {
        \T::$DI[DiConsts::DI_MYSQL] = new DB(
            new Conn(
                'mysql:host=' . \T::getConf('mysql.host') . ';port=' . \T::getConf('mysql.port') . ';dbname=' . \T::getConf('mysql.dbname') . ';charset=utf8mb4;',
                \T::getConf('mysql.username'),
                \T::getConf('mysql.password'),
                [
                    \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_PERSISTENT => true,
                ]
            )
        );
    }
}
```

## Controller

#### 内部跳转: forward

```php
// Action内
$this->forward('foo'); // 转至 当前 Controller 内 fooAction()
$this->forward('Foo', 'bar'); // 转至 当前 FooController::barAction()
```

#### Action 参数

```php
// 虽然对参数数量及顺序并无要求, 但请尽可能保持统一的参数顺序
// Cli 请求场景下: $req 是 CliRequest 实例
public function indexAction(\Tabby\Framework\Response $rsp, \Tabby\Framework\Request\HttpRequest $req)
{
}
```