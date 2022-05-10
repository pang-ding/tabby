# Cache

1) RedisCache

#### Cache 实现了 \Psr\SimpleCache\CacheInterface 接口 (delete & deleteMultiple 返回值为int, 不是接口约定的 bool)

 
#### RedisCache

```php
// 强烈建议单独为 Cache 准备一个专用 Redis 实例
// 建议 Redis 版本 >4.0

$redis = new Redis($conf);
$cache = new CacheRedis($redis);

$cache->set('foo', 'bar');
$cache->get('foo');
```