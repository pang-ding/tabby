# Redis

* 单实例
* 主从
* 哨兵

## 说明

基于 redis.so <https://github.com/phpredis/phpredis> 扩展封装

方法及参数没有改动, 只是针对 主从, 哨兵 做了封装

参照官方文档重写了 phpdoc, 语法提示可以看到, 个别存在问题的已经做了修改并标注


#### 配置

```php

// \Tabby\Store\Redis\Conf

/**
 * 连接Helper
 *
 * @param string $host           IP/UnixSocket/
 * @param int    $port
 * @param float  $connectTimeout 连接超时(秒)
 * @param int    $retryInterval  重试间隔(毫秒)
 * @param float  $readTimeout    操作超时(秒)
 * @param mixed  $persistent     是否 pconnet, 一般传bool即可. 传字符串时当做: persistent_id(长连ID) 不会复用已有其他连接, 这东西乱用可能占用大量连接数
 * @param mixed  $auth           身份验证, 允许: 'pass'|['user','pass']|['user'=>'','pass'=>'']... // Redis版本低的只能用字符串
 * @param array  $ssl            ...
 */
Conf::helper(
    string $host = '',
    int $port = 6379,
    float $connectTimeout = 0,
    int $retryInterval = 0,
    float $readTimeout = 0,
    $persistent = false,
    $auth = null,
    array $ssl = null
): array
```

#### 单实例

```php
/**
 * 单实例模式配置生成器
 *
 * @param array $master
 *
 * @return Conf
 */
Conf::single(array $master): Conf

$conf = Conf::single([ 
    Conf::helper('127.0.0.1', 16380),
]);

new MasterSlaves($conf);
```

#### 主从

```php
/**
 * 主从模式配置生成器
 *
 * @param array $master
 * @param array $slaves
 * @param bool  $onlyMaster
 *
 * @return Conf
 */
Conf::masterSlave(array $master, array $slaves, bool $onlyMaster = false): Conf

$conf = Conf::masterSlave(
    Conf::helper('127.0.0.1', 16380),
    [
        Conf::helper('127.0.0.1', 16381),
        Conf::helper('127.0.0.1', 16382),
    ]
);

new MasterSlaves($conf);
```

#### 哨兵

```php
/**
 * 哨兵模式配置生成器
 *
 * @param string    $masterName  // Redis哨兵配置文件里找
 * @param array     $sentinels   // 哨兵连接数组
 * @param array     $clientsConf // Reids配置, HOST & PORT 从哨兵取, 其余配置需要自定义
 * @param Lock|null $lock        // 锁. 发生主从切换后, 瞬时所有请求都会打到哨兵, 这个锁保证只有一个进程写配置缓存, 其余的只是使用从哨兵获得的结果
 * @param bool      $onlyMaster  // 只使用主库
 *
 * @return Conf
 */
Conf::sentinel(string $masterName, array $sentinels, array $clientsConf, ?Lock $lock = null, bool $onlyMaster = false): Conf

$conf = Conf::sentinel(
    'mymaster',
    [
        Conf::helper('127.0.0.1', 26380),
        Conf::helper('127.0.0.1', 26381),
        Conf::helper('127.0.0.1', 26382),
    ],
    Conf::helper(),
    null, // 默认使用文件锁
    true
);
new Sentinel($conf);
```