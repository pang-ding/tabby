# 日志

Tabby 不提供日志模块

接受符合 ```\Psr\Log\LoggerInterface``` 的日志实例

```php
// 如果不传入日志实例, 默认以如下方式使用 MonoLog: 
protected static function defaultLog()
{
    $formatter = new LineFormatter('[%level_name%] %message%', 'm-d, H:i:s');
    $handler   = new SyslogHandler(self::$Conf['app']['name'], LOG_LOCAL6);
    $handler->setFormatter($formatter);
    $handlers = [$handler];
    if (self::$isDebug) {
        $handlers[] = new ChromePHPHandler(MonoLogger::INFO, false);
    }

    return new MonoLogger(self::$Conf['prj']['name'], $handlers);
}
```
