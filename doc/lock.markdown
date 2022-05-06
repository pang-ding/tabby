# 锁 (Lock)

简陋的非阻塞锁. 为了避免意外, 使用时务必设置合理的超时时间

建议: 使用前看一下源码, 确定是否适用

#### 适用方式

```php
// 文件锁
$lock = new Lock(
    Lock::DRIVER_FILE,  // 驱动名
    'lock_name',        // 锁名称
    5                   // 超时(秒)
);

// MEMCACHE
$lock = new Lock(
    Lock::DRIVER_MEMCACHE, // 驱动名
    'lock_name',           // 锁名称
    5,                     // 超时(秒)
    ['memcache' => new Memcache([['127.0.0.1', 11211]], [])] // 驱动参数
);

if ($lock->lock()) {
    // lock 成功 ...

    $lock->unlock();
} else {
    // lock 失败 ...
}
```
