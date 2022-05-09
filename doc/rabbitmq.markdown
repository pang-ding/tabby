# RabbitMQ

___暂不支持复杂场景(需要临时创建Exchange/Queue, 或者消息体不是明文的情况)___

#### 默认没在composer.json里加引用, 使用的时候自己加一下

```json
"php-amqplib/php-amqplib": "^3.1"
```

#### 参考 DEMO: /demo/app/console/controllers/Rabbitmq.php

#### 配置

```php
// Bootstrap中

\T::$DI['rmq'] = new BasicQueueRabbitMQ([
    'host'    => '127.0.0.1',
    'port'    => 5672,
    'user'    => 'tabby_test',
    'password'=> 'tabby_test',
]);
// 交换机, 仅用于 publish. 如果多处 publish 使用不同的 Exchange, 请在每次 publish 前设置.
\T::$DI['rmq']->setExchange('tabby_test'); 

// 开启 publish ack, 失败时返回 false. 默认关闭, 直接返回 true
// 只接收 ack nack, 不处理 Exchange 找不到 Queue 的情况. 用状态异步做数据补偿是正途
// 参数 $publishAckTimeout 是等待 ack 超时时间 (秒 float, 默认: 3.0)
\T::$DI['rmq']->publishAck(float $publishAckTimeout = 3.0);
```

#### 发消息

```php
// $msg
\T::$DI['rmq']->publish(string $queue, string $msg);
```

#### 消费 (一般放在 Cli Worker)

```php
\T::$DI['rmq']->receive(string $queue, function (string $msg) {

    // 业务逻辑 ...

    return true; // ACK (true: ack | false: reject) 
});
```

#### 更多设置

```php
// 连接参数
$connConfig['host'];
$connConfig['port'];
$connConfig['user'];
$connConfig['password'];
$connConfig['vhost'] ?? '/';
$connConfig['insist'] ?? false;
$connConfig['login_method'] ?? 'AMQPLAIN';
$connConfig['login_response'] ?? null;
$connConfig['locale'] ?? 'en_US';
$connConfig['connection_timeout'] ?? 3.0;
$connConfig['read_write_timeout'] ?? 3.0;
$connConfig['context'] ?? null;
$connConfig['keepalive'] ?? false; // 连接时间长的场景 打开 keepalive, 并设置 heartbeat
$connConfig['heartbeat'] ?? 0;     // 心跳间隔 一般30
$connConfig['channel_rpc_timeout'] ?? 0.0;
$connConfig['ssl_protocol'] ?? null;

\T::$DI['rmq'] = new BasicQueueRabbitMQ($connConfig);

// 消息参数 (部分设置可以参考 HTTP)
$msgConfig['content_type'];         // !MIME 如果是明文建议写 text/plain, 避免麻烦
$msgConfig['content_encoding'];     // gzip ... 
$msgConfig['application_headers'];  // header数组
$msgConfig['delivery_mode'];        // !是否持久化 1:否,2:是 
$msgConfig['priority'];             // 权重(仅用于优先队列) 数值大优先级高
$msgConfig['correlation_id'];       // 发送和接收方约定的暗号, RPC回调/多线程且有状态 之类的场景用. 解决这类场景下创建临时queue资源开销大的问题
$msgConfig['reply_to'];             // request/reply模式
$msgConfig['expiration'];           // !ttl, 毫秒
$msgConfig['timestamp'];            // 时间戳
$msgConfig['message_id'];           // 
$msgConfig['type'];                 // 
$msgConfig['user_id'];              // 
$msgConfig['app_id'];               // 
$msgConfig['cluster_id'];           // 

\T::$DI['rmq']->setMessageProperties($msgConfig);

// 消费者参数
$consumeConfig['consumer_tag'] ?? '',
$consumeConfig['no_local'] ?? false,
$consumeConfig['no_ack'] ?? false,
$consumeConfig['exclusive'] ?? false,
$consumeConfig['nowait'] ?? false,

\T::$DI['rmq']->setConsumeConfig($consumeConfig);
```