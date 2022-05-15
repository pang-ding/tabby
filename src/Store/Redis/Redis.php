<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Store\Redis;

use Tabby\Error\ErrorSys;

/**
 * Redis
 *
 * 一般情况下, 操作目标不存在或类型与操作不匹配时, 返回 false
 */
class Redis
{
    const AFTER  = \Redis::AFTER;
    const BEFORE = \Redis::BEFORE;

    /* types */
    const REDIS_NOT_FOUND = \Redis::REDIS_NOT_FOUND;
    const REDIS_STRING    = \Redis::REDIS_STRING;
    const REDIS_SET       = \Redis::REDIS_SET;
    const REDIS_LIST      = \Redis::REDIS_LIST;
    const REDIS_ZSET      = \Redis::REDIS_ZSET;
    const REDIS_HASH      = \Redis::REDIS_HASH;
    const REDIS_STREAM    = \Redis::REDIS_STREAM;

    /* Add common mode constants */
    const ATOMIC = \Redis::ATOMIC;
    const MULTI  = \Redis::MULTI;

    /* scan options*/
    const OPT_SCAN      = \Redis::OPT_SCAN;
    const SCAN_RETRY    = \Redis::SCAN_RETRY;
    const SCAN_NORETRY  = \Redis::SCAN_NORETRY;
    const SCAN_PREFIX   = \Redis::SCAN_PREFIX;
    const SCAN_NOPREFIX = \Redis::SCAN_NOPREFIX;

    public $_modeIns;

    public function __construct(Conf $conf)
    {
        $mode = $conf->getMode();
        switch ($mode) {
            case Conf::MODE_SENTINEL:
                $this->_modeIns = new Sentinel($conf);

                break;
            case Conf::MODE_MASTER_SLAVE:
                $this->_modeIns = new MasterSlaves($conf);

                break;
            case Conf::MODE_SINGLE:
                $this->_modeIns = new MasterSlaves($conf);

                break;

            default:
                throw new ErrorSys("RedisConf Error: Unknown mode: '{$this->_mode}'");
        }
    }

    public function getModeIns()
    {
        return $this->_modeIns;
    }

    public function __call($name, $arguments)
    {
        return $this->_modeIns->masterExec($name, $arguments);
    }

    public function multi()
    {
        return $this->_modeIns->getMaster()->phpRedis->multi();
    }

    /* ================================ String ================================
     */
    /**
     * SET key value $options
     *
     * 将 key 的值设置为 value, 如 key 已存在则覆盖(无视类型), 且原有 TTL 将被清除
     *
     * @param string    $key
     * @param mixed     $value
     * @param int|array $options int:超时时间(秒) || array:['nx'(不存在),'xx'(存在),','ex'=>1(秒),'px'=>1000(毫秒)]
     *
     * @return bool true: 成功 || false: 失败
     *
     * @uses set('key', 'value');
     * @uses set('key','value', 10); 10秒超时
     * @uses set('key', 'value', ['nx', 'ex'=>10]);      不存在则设置, 10秒超时
     * @uses set('key', 'value', ['xx', 'px'=>1000]);    存在则设置, 1000毫秒(1秒)超时
     */
    public function set($key, $value, $options = null)
    {
        return $this->_modeIns->masterExec('set', [$key, $value, $options]);
    }

    /**
     * SETNX key value
     *
     * 仅当 key 不存在的情况下, 将 key 的值设置为 value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool true: 成功 || false: 失败
     */
    public function setNx($key, $value)
    {
        return $this->_modeIns->masterExec('setNx', [$key, $value]);
    }

    /**
     * SETEX key seconds value
     *
     * 将 key 的值设置为 value, 并设置生存时间为 seconds 秒钟
     * 如 key 已存在则覆盖(无视类型)
     * SETEX 是原子操作
     *
     * @param string $key
     * @param int    $seconds 生存时间(秒)
     * @param mixed  $value
     *
     * @return bool true: 成功
     */
    public function setEx($key, $seconds, $value)
    {
        return $this->_modeIns->masterExec('setEx', [$key, $seconds, $value]);
    }

    /**
     * PSETEX key milliseconds value
     *
     * 将 key 的值设置为 value, 并设置生存时间为 milliseconds 毫秒
     * 如 key 已存在则覆盖(无视类型)
     * PSETEX 是原子操作
     *
     * @param string $key
     * @param int    $milliseconds 生存时间(毫秒, 千分之一秒)
     * @param mixed  $value
     *
     * @return bool true: 成功
     */
    public function pSetEx($key, $milliseconds, $value)
    {
        return $this->_modeIns->masterExec('pSetEx', [$key, $milliseconds, $value]);
    }

    /**
     * GET key
     *
     * 返回与 key 相关联的值
     *
     * @param string $key
     *
     * @return string|false
     */
    public function get($key)
    {
        return $this->_modeIns->slaveExec('get', [$key]);
    }

    /**
     * GETSET key value
     *
     * 将 key 的值设为 value ，并返回键 key 在被设置之前的旧值
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return string|false 旧值
     */
    public function getSet($key, $value)
    {
        return $this->_modeIns->masterExec('getSet', [$key, $value]);
    }

    /**
     * STRLEN key
     *
     * 返回键 key 储存的字符串值的长度
     *
     * @param string $key
     *
     * @return int|false 字符串值的长度
     */
    public function strlen($key)
    {
        return $this->_modeIns->slaveExec('strlen', [$key]);
    }

    /**
     * APPEND key value
     *
     * 如果键 key 已经存在并且它的值是一个字符串, APPEND 命令将把 value 追加到键 key 现有值的末尾
     * 如果 key 不存在, APPEND 就简单地将键 key 的值设为 value , 就像执行 SET key value 一样
     * 遇到数值强转字符串
     *
     * @param string $key
     * @param string $value
     *
     * @return int|false 追加 value 之后, 键 key 的值的长度
     */
    public function append($key, $value)
    {
        return $this->_modeIns->masterExec('append', [$key, $value]);
    }

    /**
     * SETRANGE key offset value
     *
     * 从偏移量 offset 开始, 用 value 参数覆写键 key 储存的字符串值
     * 不存在的键 key 当作空白字符串处理
     * SETRANGE 命令会确保字符串足够长以便将 value 设置到指定的偏移量上, 如果键 key 原来储存的字符串长度比偏移量小(比如字符串只有 5 个字符长, 但你设置的 offset 是 10 ), 那么原字符和偏移量之间的空白将用零字节(zerobytes, "\x00" )进行填充
     * 注意: 当生成一个很长的字符串时, Redis 需要分配内存空间, 该操作有时候可能会造成服务器阻塞(block)
     *
     * @param string $key
     * @param int    $offset
     * @param string $value
     *
     * @return int|false 修改之后, 字符串值的长度
     */
    public function setRange($key, $offset, $value)
    {
        return $this->_modeIns->masterExec('setRange', [$key, $offset, $value]);
    }

    /**
     * GETRANGE key start end
     *
     * 返回键 key 储存的字符串值的指定部分, 字符串的截取范围由 start 和 end 两个偏移量决定(闭区间)
     * 负数偏移量表示从字符串的末尾开始计数, -1 表示最后一个字符
     * GETRANGE 通过保证子字符串的值域(range)不超过实际字符串的值域来处理超出范围的值域请求
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return string|false 字符串值的指定部分
     */
    public function getRange($key, $start, $end)
    {
        return $this->_modeIns->slaveExec('getRange', [$key, $start, $end]);
    }

    /**
     * INCR key
     *
     * 为键 key 储存的数字值加上一
     * 如果键 key 不存在, 那么它的值会先被初始化为 0 , 然后再执行 INCR 命令
     * 如果键 key 储存的值不能被解释为数字, 那么 INCR 命令将返回一个错误
     * 本操作的值限制在 64 位(bit)有符号数字表示之内
     * 注意: INCR 命令是一个针对字符串的操作 因为 Redis 并没有专用的整数类型, 所以键 key 储存的值在执行 INCR 命令时会被解释为十进制 64 位有符号整数
     *
     * @param string $key
     *
     * @return int|false 加一操作之后的值
     */
    public function incr($key)
    {
        return $this->_modeIns->masterExec('incr', [$key]);
    }

    /**
     * INCRBY key increment
     *
     * 为键 key 储存的数字值加上增量 increment
     * 如果键 key 不存在, 那么键 key 的值会先被初始化为 0 , 然后再执行 INCRBY 命令
     * 如果键 key 储存的值不能被解释为数字, 那么 INCRBY 命令将返回一个错误
     * 本操作的值限制在 64 位(bit)有符号数字表示之内
     *
     * @param string $key
     * @param int    $value 要增加的数值
     *
     * @return int|false 加上增量 increment 之后, 键 key 当前的值
     */
    public function incrBy($key, $value)
    {
        return $this->_modeIns->masterExec('incrBy', [$key, $value]);
    }

    /**
     * INCRBYFLOAT key increment
     *
     * 为键 key 储存的值加上浮点数增量 increment
     * 如果键 key 不存在, 那么 INCRBYFLOAT 会先将键 key 的值设为 0 , 然后再执行加法操作
     * 无论是键 key 的值还是增量 increment , 都可以使用像 2.0e7 、 3e5 、 90e-2 那样的指数符号(exponential notation)来表示
     * 执行 INCRBYFLOAT 命令之后的值总是以同样的形式储存, 它们总是由一个数字, 一个（可选的）小数点和一个任意长度的小数部分组成（比如 3.14 、 69.768), 小数部分尾随的 0 会被移除, 如果可能的话, 命令还会将浮点数转换为整数（比如 3.0 会被保存成 3 ）
     * 此外, 无论加法计算所得的浮点数的实际精度有多长, INCRBYFLOAT 命令的计算结果最多只保留小数点的后十七位
     *
     * @param string $key
     * @param float  $value 要增加的数值
     *
     * @return float|false 加上增量 increment 之后, 键 key 的值
     */
    public function incrByFloat($key, $value)
    {
        return $this->_modeIns->masterExec('incrByFloat', [$key, $value]);
    }

    /**
     * DECR key
     *
     * 为键 key 储存的数字值减去一
     * 如果键 key 不存在, 那么键 key 的值会先被初始化为 0 , 然后再执行 DECR 操作
     * 本操作的值限制在 64 位(bit)有符号数字表示之内
     *
     * @param string $key
     *
     * @return int|false 递减后的结果数值
     */
    public function decr($key)
    {
        return $this->_modeIns->masterExec('decr', [$key]);
    }

    /**
     * DECRBY key decrement
     *
     * 将键 key 储存的整数值减去减量 decrement
     * 如果键 key 不存在, 那么键 key 的值会先被初始化为 0 , 然后再执行 DECRBY 命令
     * 本操作的值限制在 64 位(bit)有符号数字表示之内
     *
     * @param string $key
     * @param int    $value 要减少的数值
     *
     * @return int|false 执行减法操作之后的值
     */
    public function decrBy($key, $value)
    {
        return $this->_modeIns->masterExec('decrBy', [$key, $value]);
    }

    /**
     * MSET key value [key value …]
     *
     * 同时为多个键设置值
     * 如果某个给定键已经存在, 那么 MSET 将使用新值去覆盖旧值
     * MSET 是一个原子性(atomic)操作, 所有给定键都会在同一时间内被设置, 不会出现某些键被设置了但是另一些键没有被设置的情况
     *
     * @param array $keysValues
     *
     * @return bool true:成功
     */
    public function mSet($keysValues)
    {
        return $this->_modeIns->masterExec('mSet', [$keysValues]);
    }

    /**
     * MSETNX key value [key value …]
     *
     * 所有给定键都不存在时, 为所有给定键设置值
     * MSETNX 是一个原子操作, 所有给定键要么就全部都被设置, 要么就全部都不设置
     *
     * @param array $keysValues
     *
     * @return bool true:成功 false:失败
     */
    public function mSetNx($keysValues)
    {
        return $this->_modeIns->masterExec('mSetNx', [$keysValues]);
    }

    /**
     * MGET key [key …]
     *
     * 返回给定的一个或多个字符串键的值
     * 如果给定的字符串键里面, 有某个键不存在, 那么这个键的值将以特殊值 nil 表示
     *
     * @param array $keys
     *
     * @return mixed[] 结果数组, 包含了所有给定键的值
     */
    public function mGet($keys)
    {
        return $this->_modeIns->slaveExec('mGet', [$keys]);
    }

    // ================================ Database ================================

    /**
     * EXISTS key
     *
     * 检查给定 key 是否存在
     *
     * @param string[] ...$key
     *
     * @return int 经检查发现存在的key数量
     */
    public function exists(...$args)
    {
        return $this->_modeIns->slaveExec('exists', $args);
    }

    /**
     * TYPE key
     *
     * 返回 key 所储存的值的类型
     *
     * @param string $key
     *
     * @return int 数据类型
     *             none:    \Redis::REDIS_NOT_FOUND
     *             string:  \Redis::REDIS_STRING
     *             list:    \Redis::REDIS_LIST
     *             set:     \Redis::REDIS_SET
     *             zset:    \Redis::REDIS_ZSET
     *             hash:    \Redis::REDIS_HASH
     *             stream:  \Redis::REDIS_STREAM
     *
     */
    public function type($key)
    {
        \Redis::REDIS_STREAM;

        return $this->_modeIns->slaveExec('type', [$key]);
    }

    /**
     * RENAME key newkey
     *
     * 将 key 改名为 newkey
     * 当 key 和 newkey 相同, 或者 key 不存在时, 返回一个错误
     * 当 newkey 已经存在时,  RENAME 命令将覆盖旧值
     *
     * @param string $srckey
     * @param string $dstkey
     *
     * @return bool true: 成功 || false: 失败
     *              改名成功时提示 OK , 失败时候返回一个错误
     */
    public function rename($srckey, $dstkey)
    {
        return $this->_modeIns->masterExec('rename', [$srckey, $dstkey]);
    }

    /**
     * 清空当前选定的DB
     *
     * $async Redis4.0之后支持 flushdb async, key是立即消失的, 数据异步删除, 避免阻塞线程
     *
     * @param bool $async
     *
     * @return bool true: 成功
     */
    public function flushDb($async = true)
    {
        return $this->_modeIns->masterExec('flushDb', [$async]);
    }

    /**
     * 清空所有DB
     *
     * $async Redis4.0之后支持 flushall async, key是立即消失的, 数据异步删除, 避免阻塞线程
     *
     * @param bool $async
     *
     * @return bool true: 成功
     */
    public function flushAll($async)
    {
        return $this->_modeIns->masterExec('flushAll', [$async]);
    }

    /**
     * RENAMENX key newkey
     *
     * newkey 不存在时, 将 key 改名为 newkey
     * key 不存在时, 返回一个错误
     *
     * @param string $srckey
     * @param string $dstkey
     *
     * @return bool true: 成功 || false: 失败
     *              修改成功时, 返回 1 ；如果 newkey 已经存在, 返回 0
     */
    public function renameNx($srckey, $dstkey)
    {
        return $this->_modeIns->masterExec('renameNx', [$srckey, $dstkey]);
    }

    /**
     * DEL key [key …]
     *
     * 删除给定的一个或多个 key
     * 不存在的 key 会被忽略
     *
     * @param string[] ...$key
     *
     * @return int 被删除 key 的数量
     */
    public function del(...$args)
    {
        return $this->_modeIns->masterExec('del', $args);
    }

    /**
     * UNLINK key [key …] (Redis > 4.0)
     *
     * 和 del 的区别在于数据是否异步删除(key都是立刻消失的):
     *      del     同步删除 dbSyncDelete
     *      unlink  可能异步删除(是否异步取决于内容大小) dbAsyncDelete
     *
     * 个人理解:
     *      redis是单线程模型
     *      删除导致阻塞会产生负载抖动
     *      所以有这种设计, 异步搞耗时操作
     * 备注: unlink在删除前根据数据类型不同做了一些判断
     * 结论: 删除 bigkey 用 unlink, 普通业务逻辑下不用纠结
     *
     * @param string ...$key
     *
     * @return int 被删除 key 的数量
     */
    public function unlink(...$args)
    {
        return $this->_modeIns->masterExec('unlink', $args);
    }

    /**
     * RANDOMKEY
     *
     * 从当前数据库中随机返回一个 key (不删除)
     *
     * @return string 获取到的key
     *                当数据库不为空时, 返回一个 key  当数据库为空时, 返回 nil
     */
    public function randomKey()
    {
        return $this->_modeIns->masterExec('randomKey');
    }

    /**
     * KEYS pattern
     *
     * 查找所有符合给定模式 pattern 的 key
     * pattern * 匹配数据库中所有 key
     * pattern h?llo 匹配 hello ,  hallo 和 hxllo 等
     * pattern h*llo 匹配 hllo 和 heeeeello 等
     * pattern h[ae]llo 匹配 hello 和 hallo , 但不匹配 hillo
     * 特殊符号用 \ 转义
     * 注意: KEYS 的速度非常快, 但在大库中使用仍然存在性能问题, 如需从数据集中查找 key , 最好还是使用 集合 结构
     *
     * @param string $pattern 模式
     *
     * @return array string[] 符合给定模式的 key 列表
     */
    public function keys($pattern)
    {
        return $this->_modeIns->slaveExec('keys', [$pattern]);
    }

    /**
     * SCAN cursor [MATCH pattern] [COUNT count]
     *
     * 增量迭代库中的key (keyspace)
     *
     * pattern: 查找所有符合给定模式 pattern 的 key
     * pattern * 匹配数据库中所有 key
     * pattern h?llo 匹配 hello ,  hallo 和 hxllo 等
     * pattern h*llo 匹配 hllo 和 heeeeello 等
     * pattern h[ae]llo 匹配 hello 和 hallo , 但不匹配 hillo
     *
     * @param int    &$cursor 游标必须传, Redis 并发处理迭代时的状态记录, 客户端不需要关心这个参数
     * @param string $pattern
     * @param int    $count   每次迭代返回的记录限制, 默认为 10, 每次返回的数量不一定相同
     *
     * @return array|false
     */
    public function scan(&$cursor, $pattern = null, $count = 0)
    {
        return $this->_modeIns->slaveExec('scan', [&$cursor, $pattern, $count]);
    }

    /**
     * SORT key option
     *
     * 返回或保存给定集合或列表 key 中经过排序的元素
     * 注意: 这个操作需要访问master(无论是否加store参数)
     * 注意: 对字符串排序需要设alpha=true
     * $option:
     * [
     * 'sort' => 'asc' or 'desc',
     * 'by' => 'some_pattern_*',
     * 'limit' => [0, 1],
     * 'get' => 'some_other_pattern_*' or an array of patterns,
     * 'alpha' => TRUE, // 如果为 TRUE 则排序对象为字符串 (默认: 数字)
     * 'store' => 'external-key' // 将排序结果保存到给定的键上
     * ]
     *
     * @param string $key
     * @param array  $option 规则
     *
     * @return int|array 排序后的结果数组 || 使用 STORE 参数, 返回排序结果的元素数量
     */
    public function sort($key, $option = [])
    {
        return $this->_modeIns->masterExec('sort', [$key, $option]);
    }

    /**
     * EXPIRE key seconds
     *
     * 为给定 key 设置生存时间
     * INCR / LPUSH / HSET / RENAME 这类操作不会修改 key 本身的生存时间
     * 如果 RENAME 目标key 已存在且设置了生存时间, 目标key 数据和生存时间都会被删除
     * PERSIST 命令可以在不删除 key 的情况下, 移除生存时间, 让 key 重新成为一个 persistent key
     *
     * @param string $key
     * @param int    $ttl 超时时间(秒)
     *
     * @return bool true: 成功 || false: 失败
     */
    public function expire($key, $ttl)
    {
        return $this->_modeIns->masterExec('expire', [$key, $ttl]);
    }

    /**
     * EXPIREAT key timestamp
     *
     * 用时间戳设置过期时间
     *
     * @param string $key
     * @param int    $timestamp Unix时间戳 (秒)
     *
     * @return bool true: 成功 || false: 失败
     */
    public function expireAt($key, $timestamp)
    {
        return $this->_modeIns->masterExec('expireAt', [$key, $timestamp]);
    }

    /**
     * TTL key
     *
     * 以秒为单位, 返回给定 key 的剩余生存时间
     *
     * @param string $key
     *
     * @return int 剩余生存时间 || -1: 没有设置剩余生存时间 || -2: key 不存在
     */
    public function ttl($key)
    {
        return $this->_modeIns->slaveExec('ttl', [$key]);
    }

    /**
     * PERSIST key
     *
     * 移除给定 key 的生存时间
     *
     * @param string $key
     *
     * @return bool
     *              当生存时间移除成功时, 返回 1 .如果 key 不存在或 key 没有设置生存时间, 返回 0
     */
    public function persist($key)
    {
        return $this->_modeIns->masterExec('persist', [$key]);
    }

    /**
     * PEXPIRE key milliseconds
     *
     * 为给定 key 设置毫秒级生存时间
     * INCR / LPUSH / HSET / RENAME 这类操作不会修改 key 本身的生存时间
     * 如果 RENAME 目标key 已存在且设置了生存时间, 目标key 数据和生存时间都会被删除
     * PERSIST 命令可以在不删除 key 的情况下, 移除生存时间, 让 key 重新成为一个 persistent key
     *
     * @param string $key
     * @param int    $ttl 超时时间(毫秒, 千分之一秒)
     *
     * @return bool true: 成功
     *              设置成功, 返回 1key 不存在或设置失败, 返回 0
     */
    public function pexpire($key, $ttl)
    {
        return $this->_modeIns->masterExec('pexpire', [$key, $ttl]);
    }

    /**
     * PEXPIREAT key milliseconds-timestamp
     *
     * 用时间戳(毫秒)设置过期时间
     *
     * @param string $key
     * @param int    $timestamp Unix时间戳 (毫秒, 千分之一秒)
     *
     * @return bool true: 成功 || false: 失败
     */
    public function pExpireAt($key, $timestamp)
    {
        return $this->_modeIns->masterExec('pExpireAt', [$key, $timestamp]);
    }

    /**
     * PTTL key
     *
     * 以毫秒为单位返回 key 的剩余生存时间
     * 否则, 以毫秒为单位, 返回 key 的剩余生存时间 Note
     * 在 Redis 2.8 以前, 当 key 不存在, 或者 key 没有设置剩余生存时间时, 命令都返回 -1
     *
     * @param string $key
     *
     * @return int 剩余生存时间(毫秒) || -1: 没有设置剩余生存时间 || -2: key 不存在
     */
    public function pttl($key)
    {
        return $this->_modeIns->slaveExec('pttl', [$key]);
    }

    // ================================ Hashes ================================

    /**
     * HSET hash field value
     *
     * 将哈希表 hash 中域 field 的值设置为 value
     * 如果给定的哈希表并不存在, 那么一个新的哈希表将被创建并执行 HSET 操作
     * 如果域 field 已经存在于哈希表中, 那么它的旧值将被新值 value 覆盖
     * 注意: 成功返回值不是true, 而是 1 或 0
     *
     * @param string $key
     * @param string $field
     * @param mixed  $value
     *
     * @return int|false 1: 设置成功(field不存在) || 0: 设置成功(field已存在) || false: 失败
     */
    public function hSet($key, $field, $value)
    {
        return $this->_modeIns->masterExec('hSet', [$key, $field, $value]);
    }

    /**
     * HSETNX hash field value
     *
     * 当域 field 尚未存在于哈希表的情况下, 将它的值设置为 value
     * 如果给定域已经存在于哈希表当中, 那么命令将放弃执行设置操作
     * 如果哈希表 hash 不存在, 那么一个新的哈希表将被创建并执行 HSETNX 命令
     *
     * @param string $key
     * @param string $field
     * @param mixed  $value
     *
     * @return bool true: 成功(field不存在) || 0: 失败(field已存在 or 不是hash类型)
     */
    public function hSetNx($key, $field, $value)
    {
        return $this->_modeIns->masterExec('hSetNx', [$key, $field, $value]);
    }

    /**
     * HGET hash field
     *
     * 返回哈希表中给定域的值
     *
     * @param string $key
     * @param string $field
     *
     * @return string|false 字符串:给定域的值 || false: 域不存在或哈希表不存在
     */
    public function hGet($key, $field)
    {
        return $this->_modeIns->slaveExec('hGet', [$key, $field]);
    }

    /**
     * HEXISTS hash field
     *
     * 检查给定域 field 是否存在于哈希表 hash 当中
     *
     * @param string $key
     * @param string $field
     *
     * @return bool true:存在 false:不存在
     */
    public function hExists($key, $field)
    {
        return $this->_modeIns->slaveExec('hExists', [$key, $field]);
    }

    /**
     * HDEL key field [field …]
     *
     * 删除哈希表 key 中的一个或多个指定域, 不存在的域将被忽略
     *
     * 返回值:
     * 被成功移除的域的数量, 不包括被忽略的域
     *
     * @param string $key
     * @param string ...$field
     *
     * @return int|false 被成功移除的域的数量, 不包括被忽略的域 || false: 哈希表不存在
     */
    public function hDel($key, ...$field)
    {
        return $this->_modeIns->masterExec('hDel', array_merge([$key], $field));
    }

    /**
     * HLEN key
     * 返回哈希表 key 中域的数量
     *
     * @param string $key
     *
     * @return int|false int: 哈希表中域的数量 || false: 哈希表不存在
     */
    public function hLen($key)
    {
        return $this->_modeIns->slaveExec('hLen', [$key]);
    }

    /**
     * HSTRLEN key field (Redis >= 3.2.0)
     *
     * @param string $key
     * @param string $field
     *
     * @return int|false int: 相关联的值的字符串长度 || false: 哈希表或域不存在
     *                   如果给定的键或者域不存在,  那么命令返回 0
     */
    public function hStrLen($key, $field)
    {
        return $this->_modeIns->slaveExec('hStrLen', [$key, $field]);
    }

    /**
     * HINCRBY key field increment
     *
     * 为哈希表 key 中的域 field 的值加上增量 increment
     * 增量也可以为负数, 相当于对给定域进行减法操作
     * 如果 key 不存在, 一个新的哈希表被创建并执行 HINCRBY 命令
     * 如果域 field 不存在, 那么在执行命令前, 域的值被初始化为 0
     * 本操作的值被限制在 64 位(bit)有符号数字表示之内
     *
     * @param string $key
     * @param string $field
     * @param int    $value
     *
     * @return long 增加后的值
     *              对一个储存字符串值的域 field 执行 HINCRBY 命令将造成一个错误
     */
    public function hIncrBy($key, $field, $value)
    {
        return $this->_modeIns->masterExec('hIncrBy', [$key, $field, $value]);
    }

    /**
     * HINCRBYFLOAT key field increment
     *
     * 为哈希表 key 中的域 field 加上浮点数增量 increment
     * 如果哈希表中没有域 field , 那么 HINCRBYFLOAT 会先将域 field 的值设为 0 , 然后再执行加法操作
     * 如果键 key 不存在, 那么 HINCRBYFLOAT 会先创建一个哈希表, 再创建域 field , 最后再执行加法操作
     *
     *
     * @param string $key
     * @param string $field
     * @param float  $value
     *
     * @return float 执行加法操作之后 field 域的值
     *               当以下任意一个条件发生时, 返回一个错误:
     *               域 field 的值不是字符串类型
     *               域 field 当前的值或给定的增量 increment 不能解释(parse)为双精度浮点数
     */
    public function hIncrByFloat($key, $field, $value)
    {
        return $this->_modeIns->masterExec('hIncrByFloat', [$key, $field, $value]);
    }

    /**
     * HMSET key field value [field value …]
     *
     * 同时将多个 field-value (域-值)对设置到哈希表 key 中
     * 此命令会覆盖哈希表中已存在的域
     * 如果 key 不存在, 一个空哈希表被创建并执行 HMSET 操作
     *
     * @param string   $key
     * @param string[] $fieldsValues
     *
     * @return bool true:成功
     *              当 key  不是哈希表(hash)类型时, 返回一个错误
     */
    public function hMSet($key, $fieldsValues)
    {
        return $this->_modeIns->masterExec('hMSet', [$key, $fieldsValues]);
    }

    /**
     * HMGET key field [field …]
     *
     * 返回哈希表 key 中, 一个或多个给定域的值
     *
     * @param string   $key
     * @param string[] $fields
     *
     * @return array|bool 数组:值 false:key不存在
     *                    一个包含多个给定域的关联值的表, 表值的排列顺序和给定域参数的请求顺序一样
     *                    如果给定的域不存在于哈希表, 那么返回一个 nil 值
     *                    因为不存在的 key 被当作一个空哈希表来处理, 所以对一个不存在的 key 进行 HMGET 操作将返回一个只带有 nil 值的表
     */
    public function hMGet($key, $fields)
    {
        return $this->_modeIns->slaveExec('hMGet', [$key, $fields]);
    }

    /**
     * HKEYS key
     *
     * 返回哈希表 key 中的所有域
     *
     * @param string $key
     *
     * @return array|bool 数组:值 false:key不存在
     *                    一个包含哈希表中所有域的表
     *                    当 key 不存在时, 返回一个空表
     */
    public function hKeys($key)
    {
        return $this->_modeIns->slaveExec('hKeys', [$key]);
    }

    /**
     * HVALS key
     *
     * 返回哈希表 key 中所有域的值
     *
     * @param string $key
     *
     * @return array|bool 数组:值 false:key不存在
     *                    一个包含哈希表中所有值的表
     *                    当 key 不存在时, 返回一个空表
     */
    public function hVals($key)
    {
        return $this->_modeIns->slaveExec('hVals', [$key]);
    }

    /**
     * HGETALL key
     *
     * 返回哈希表 key 中, 所有的域和值
     *
     * @param string $key
     *
     * @return array|bool 数组:值 false:key不存在
     *                    以列表形式返回哈希表的域和域的值
     *                    若 key 不存在, 返回空列表
     */
    public function hGetAll($key)
    {
        return $this->_modeIns->slaveExec('hGetAll', [$key]);
    }

    /**
     * HSCAN cursor [MATCH pattern] [COUNT count]
     *
     * 增量迭代哈希表中的键值对
     *
     * pattern: 查找所有符合给定模式 pattern 的 key
     * pattern * 匹配数据库中所有 key
     * pattern h?llo 匹配 hello ,  hallo 和 hxllo 等
     * pattern h*llo 匹配 hllo 和 heeeeello 等
     * pattern h[ae]llo 匹配 hello 和 hallo , 但不匹配 hillo
     *
     * @param string $key
     * @param int    &$cursor 游标必须传, Redis 并发处理迭代时的状态记录, 客户端不需要关心这个参数
     * @param string $pattern
     * @param int    $count   每次迭代返回的记录限制, 默认为 10, 每次返回的数量不一定相同
     *
     * @return array|false
     */
    public function hScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->_modeIns->slaveExec('hScan', [$key, &$cursor, $pattern, $count]);
    }

    // ================================ Lists ==================================

    /**
     * LPUSH key value [value …]
     *
     * 将一个或多个值 value 依次插入到列表 key 的表头
     * 如果 key 不存在，创建并执行 LPUSH 操作。
     *
     * @param string  $key
     * @param mixed[] $values
     *
     * @return int|false 执行命令后，列表的长度
     */
    public function lPush($key, ...$values)
    {
        return $this->_modeIns->masterExec('lPush', array_merge([$key], $values));
    }

    /**
     * LPUSHX key value
     *
     * 将值 value 插入到列表 key 的表头
     * 当 key 不存在时， 什么也不做
     *
     * @param string $key
     * @param string $value
     *
     * @return int 执行命令后，列表的长度
     */
    public function lPushx($key, $value)
    {
        return $this->_modeIns->masterExec('lPushx', [$key, $value]);
    }

    /**
     * TODO
     * RPUSH key value [value …]
     *
     * 将一个或多个值 value 依次插入到列表 key 的表尾
     * 如果 key 不存在，创建并执行 RPUSH 操作
     *
     * @param string   $key
     * @param string[] $values
     *
     * @return int|false 执行命令后，列表的长度
     */
    public function rPush($key, ...$values)
    {
        return $this->_modeIns->masterExec('rPush', array_merge([$key], $values));
    }

    /**
     * RPUSHX key value
     *
     * 将值 value 插入到列表 key 的表尾
     * 当 key 不存在时， 什么也不做
     *
     * @param string $key
     * @param string $value
     *
     * @return int|false 执行命令后，列表的长度
     */
    public function rPushx($key, $value)
    {
        return $this->_modeIns->masterExec('rPushx', [$key, $value]);
    }

    /**
     * LPOP key
     *
     * 移除并返回列表 key 的头元素。
     *
     * @param string $key
     *
     * @return string|false 列表的头元素
     */
    public function lPop($key)
    {
        return $this->_modeIns->masterExec('lPop', [$key]);
    }

    /**
     * RPOP key
     *
     * 移除并返回列表 key 的尾元素。
     *
     * @param string $key
     *
     * @return string|false 列表的尾元素
     */
    public function rPop($key)
    {
        return $this->_modeIns->masterExec('rPop', [$key]);
    }

    /**
     * RPOPLPUSH srcKey dstKey
     *
     * 同时执行 RPOP LPUSH 将列表 srcKey 中尾元素弹出后插入到列表 dstKey 的头部, 并将元素返回给客户端
     * RPOPLPUSH 是原子操作
     * srcKey dstKey 可以是同一个列表
     *
     * @param string $srcKey
     * @param string $dstKey
     *
     * @return string|false 被弹出的元素
     */
    public function rPoplPush($srcKey, $dstKey)
    {
        return $this->_modeIns->masterExec('rPoplPush', [$srcKey, $dstKey]);
    }

    /**
     * LREM key count value
     *
     * 从表头(L)开始，移除列表中与 value 相等的元素，count: 限制移除个数
     * count < 0 时, 从表尾(R)开始
     * count = 0 时, 移除表中所有与 value 相等的元素
     *
     * @param string $key
     * @param string $value
     * @param int    $count
     *
     * @return int 被移除元素的数量
     */
    public function lRem($key, $value, $count)
    {
        return $this->_modeIns->masterExec('lRem', [$key, $value, $count]);
    }

    /**
     * LLEN key
     *
     * 返回列表 key 的长度。
     *
     * @param string $key
     *
     * @return int|false 列表 key 的长度 || 不存在:0 || 不是列表:false
     */
    public function lLen($key)
    {
        return $this->_modeIns->slaveExec('lLen', [$key]);
    }

    /**
     * LINDEX key index
     *
     * 返回列表 key 中, 下标为 index 的元素
     * 可以使用负数下标, 以 -1 表示列表的最后一个元素
     *
     * @param string $key
     * @param int    $index
     *
     * @return string|false 字符串
     */
    public function lIndex($key, $index)
    {
        return $this->_modeIns->slaveExec('lIndex', [$key, $index]);
    }

    /**
     * LINSERT key BEFORE|AFTER pivot value
     *
     * 将值 value 插入到列表 key 当中, 位于值 pivot 之前或之后
     * 当 pivot 不存在于列表 key 时, 不执行任何操作
     * 当 key 不存在时,  key 被视为空列表, 不执行任何操作
     *
     * @param string $key
     * @param string $position \Redis::BEFORE | \Redis::AFTER
     * @param string $pivot
     * @param string $value
     *
     * @return int|false 操作完成之后, 列表的长度 || 没有找到 pivot: -1 || key不存在: 0 || 不是列表类型: false
     */
    public function lInsert($key, $position, $pivot, $value)
    {
        return $this->_modeIns->masterExec('lInsert', [$key, $position, $pivot, $value]);
    }

    /**
     * lSet 设置指定key的list中listIndex的值
     * LSET key index value
     *
     * 将列表 key 下标为 index 的元素的值设置为 value
     *
     * @param string $key
     * @param int    $index
     * @param string $value
     *
     * @return bool true:成功 false:失败
     *              当 index 参数超出范围, 或对一个空列表( key 不存在)进行 LSET 时, 返回false
     */
    public function lSet($key, $index, $value)
    {
        return $this->_modeIns->masterExec('lSet', [$key, $index, $value]);
    }

    /**
     * lRange 获取指定key的list中指定范围的元素
     *
     * LRANGE key start stop
     *
     * 返回列表 key 中指定区间内的元素, 区间以偏移量 start 和 stop 指定
     * 可以使用负数下标, 以 -1 表示列表的最后一个元素
     * LRANGE list 0 10 , 结果是一个包含11个元素
     * 下标越界不会引起错误
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return array
     *               一个列表, 包含指定区间内的元素
     */
    public function lRange($key, $start, $end)
    {
        return $this->_modeIns->slaveExec('lRange', [$key, $start, $end]);
    }

    /**
     * LTRIM key start stop
     *
     * 让列表只保留指定区间内的元素, 不在指定区间之内的元素都将被删除
     * LTRIM list 0 2 , 表示只保留列表 list 的前三个元素, 其余元素全部删除
     * 可以使用负数下标, 以 -1 表示列表的最后一个元素
     * LTRIM list 0 10 , 结果是一个包含11个元素
     * 下标越界不会引起错误
     *
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return
     * 命令执行成功时, 返回 ok
     * 当 key 不是列表类型时, 返回一个错误
     */
    public function lTrim($key, $start, $stop)
    {
        return $this->_modeIns->masterExec('lTrim', [$key, $start, $stop]);
    }

    // ================================ ZSet ==================================
    /**
     * ZADD key [options] score member [[score member] [score member] …]
     *
     * 将一个或多个 member 元素及其 score 值加入到有序集 key 当中
     * 如果 key 不存在，则创建
     * 如果 member 已存在，使用新的 score 重新插入这个 member
     * score 值可以是整数值或双精度浮点数。
     *
     * @param string $key
     * @param array   [$options]
     * @param mixed  $score
     * @param string $member
     * @param mixed   [$score]...
     * @param string  [$member]...
     *
     * @return int 新成员数量，不包括那些被更新的
     */
    public function zAdd($key, ...$args)
    {
        return $this->_modeIns->masterExec('zAdd', array_merge([$key], $args));
    }

    /**
     * ZSCORE key member
     *
     * 返回有序集 key 中，member 的 score 值。
     *
     * @param string $key
     * @param string $member
     *
     * @return string score
     */
    public function zScore($key, $member)
    {
        return $this->_modeIns->slaveExec('zScore', [$key, $member]);
    }

    /**
     * ZINCRBY key increment member
     *
     * 为有序集 key 的成员 member 的 score 值加上增量 increment (接受负数)
     *
     * @param string $key
     * @param mixed  $increment
     * @param string $member
     *
     * @return string score
     */
    public function zIncrBy($key, $increment, $member)
    {
        return $this->_modeIns->masterExec('zIncrBy', [$key, $increment, $member]);
    }

    /**
     * ZCARD key
     *
     * 返回有序集 key 中 member 数量
     *
     * @param string $key
     *
     * @return int
     */
    public function zCard($key)
    {
        return $this->_modeIns->slaveExec('zCard', [$key]);
    }

    /**
     * ZCOUNT key min max
     *
     * 返回有序集 key 中，score 值在 min 和 max 之间(闭区间)的 member 数量
     *
     * @param string $key
     * @param string $min
     * @param string $max
     *
     * @return int|false
     */
    public function zCount($key, $min, $max)
    {
        return $this->_modeIns->slaveExec('zCount', [$key, $min, $max]);
    }

    /**
     * ZPOPMIN key count
     *
     * 返回有序集 key 中, 根据 count 指定的数量, 弹出且得分最低的 member 并返回 [member => score] 键值对数组
     *
     * @param string $key
     * @param int    $count
     *
     * @return array
     */
    public function zPopMin($key, $count)
    {
        return $this->_modeIns->masterExec('zPopMin', [$key, $count]);
    }

    /**
     * ZPOPMAX key count
     *
     * 返回有序集 key 中, 根据 count 指定的数量, 弹出且得分最高的 member 并返回 [member => score] 键值对数组
     *
     * @param string $key
     * @param int    $count
     *
     * @return array
     */
    public function zPopMax($key, $count)
    {
        return $this->_modeIns->masterExec('zPopMax', [$key, $count]);
    }

    /**
     * ZRANGE key start stop [WITHSCORES]
     *
     * 返回有序集 key 中，指定区间内的 member
     * 排序规则: score 递增 / score 相同按字典序排序
     * 默认只返回 member 数组，WITHSCORES = true 时 返回 [member => score] 键值对
     *
     * @param string $key
     * @param int    $start      开始下标
     * @param int    $stop       结束下标
     * @param bool   $withScores
     *
     * @return array
     */
    public function zRange($key, $start, $stop, $withScores = false)
    {
        return $this->_modeIns->slaveExec('zRange', [$key, $start, $stop, $withScores]);
    }

    /**
     * ZREVRANGE key start stop [WITHSCORES]
     *
     * 返回有序集 key 中，指定区间内的 member
     * 排序规则: score 递减 / score 相同按字典逆序排序
     * 默认只返回 member 数组，WITHSCORES = true 时 返回 [member => score] 键值对
     *
     * @param string $key
     * @param int    $start      开始下标
     * @param int    $stop       结束下标
     * @param bool   $withScores
     *
     * @return array
     */
    public function zRevRange($key, $start, $stop, $withScores = false)
    {
        return $this->_modeIns->slaveExec('zRevRange', [$key, $start, $stop, $withScores]);
    }

    /**
     * ZRANGEBYSCORE key min max [optiong WITHSCORES [offset count]]
     *
     * [顺序] 返回有序集合中指定分数区间的成员列表。有序集成员按分数值递增排列
     * min 和 max 默认闭区间, 可以使用 ( 指定开区间. '+inf' 和 '-inf' 代表正负无穷
     * 例如: '(100', '200' 表示 100 < X <= 200
     * options:
     *    WITHSCORES  返回成员和分数
     *    LIMIT offset count  类似sql
     * ['withscores' => TRUE, 'limit' => [1, 1]]
     *
     * @param string $key
     * @param string $min
     * @param string $max
     * @param array  $options
     *
     * @return array
     */
    public function zRangeByScore($key, $min, $max, $options = [])
    {
        return $this->_modeIns->slaveExec('zRangeByScore', [$key, $min, $max, $options]);
    }

    /**
     * ZREVRANGEBYSCORE key max min [optiong WITHSCORES [offset count]]
     *
     * [逆序] 返回有序集合中指定分数区间的成员列表。有序集成员按分数值递减排列
     * min 和 max 默认闭区间, 可以使用 '(' 指定开区间. '+inf' 和 '-inf' 代表正负无穷
     * 例如: '(100', '200' 表示 100 < X <= 200
     * options:
     *    WITHSCORES  返回成员和分数
     *    LIMIT offset count  类似sql
     * ['withscores' => TRUE, 'limit' => [1, 1]]
     *
     * @param string $key
     * @param string $min
     * @param string $max
     * @param array  $options
     *
     * @return array
     */
    // public function zRevRangeByScore($key, $min, $max, $options = [])
    // {
    //     $options = ['withscores' => true, 'limit' => [0, 10]];
    //     var_dump($key, $min, $max, $options);
    //     var_dump($this->_modeIns->masterExec('zRangeByScore', [$key, $min, $max, $options]));
    //     var_dump($this->_modeIns->getMaster()->phpRedis->zRevRangeByScore($key, $min, $max, $options));exit;
    //     return $this->_modeIns->slaveExec('zRevRangeByScore', [$key, $min, $max, $options]);
    // }

    /**
     * ZRANK key member
     *
     * [顺序] 返回有序集 key 中 member 按 score 值递增排名
     *
     * @param string $key
     * @param string $member
     *
     * @return int|false
     */
    public function zRank($key, $member)
    {
        return $this->_modeIns->slaveExec('zRank', [$key, $member]);
    }

    /**
     * ZREVRANK key member
     *
     * [逆序] 返回有序集 key 中 member 按 score 值递增排名
     *
     * @param string $key
     * @param string $member
     *
     * @return int|false
     */
    public function zRevRank($key, $member)
    {
        return $this->_modeIns->slaveExec('zRevRank', [$key, $member]);
    }

    /**
     * ZREM key member [member …]
     *
     * 移除有序集 key 中的一个或多个 member ，不存在的将被忽略。
     *
     * @param string $key
     * @param string ...$member
     *
     * @return int 成功移除的 member 数量，不包括不存在的
     */
    public function zRem($key, ...$member)
    {
        return $this->_modeIns->masterExec('zRem', array_merge([$key], $member));
    }

    /**
     * ZREMRANGEBYRANK key start stop
     *
     * 移除有序集 key 中，指定排名 rank 闭区间内的所有 member
     *
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return int 成功移除的成员数量
     */
    public function zRemRangeByRank($key, $start, $stop)
    {
        return $this->_modeIns->masterExec('zRemRangeByRank', [$key, $start, $stop]);
    }

    /**
     * ZREMRANGEBYSCORE key min max
     *
     * 移除有序集 key 中，所有 score 值介于 min 和 max 之间(闭区间)的 member
     * min 和 max 默认闭区间, 可以使用 '(' 指定开区间. '+inf' 和 '-inf' 代表正负无穷
     * 例如: '(100', '200' 表示 100 < X <= 200
     *
     * 被移除成员的数量。
     *
     * @param string $key
     * @param string $min
     * @param string $max
     *
     * @return int 被移除成员的数量
     */
    public function zRemRangeByScore($key, $min, $max)
    {
        return $this->_modeIns->masterExec('zRemRangeByScore', [$key, $min, $max]);
    }

    /**
     * ZRANGEBYLEX key min max [offset] [limit]
     *
     * 通过字典序区间返回有序集合的 member
     * min max 语法:
     * '+' 和 '-' 表示正负无穷
     * '(' 和 '[' 表示开闭区间 '[a', '(c' 表示 'a' <= X < 'c'
     *
     * @param string $key
     * @param string $min
     * @param string $max
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function zRangeByLex($key, $min, $max, $offset = 0, $limit = -1)
    {
        return $this->_modeIns->slaveExec('zRangeByLex', [$key, $min, $max, $offset, $limit]);
    }

    /**
     * ZLEXCOUNT key min max
     *
     * 通过字典序区间返回有序集合的 member 数量
     * min max 语法:
     * '+' 和 '-' 表示正负无穷
     * '(' 和 '[' 表示开闭区间 '[a', '(c' 表示 'a' <= X < 'c'
     *
     * @param string $key
     * @param string $min
     * @param string $max
     *
     * @return int
     */
    public function zLexCount($key, $min, $max)
    {
        return $this->_modeIns->slaveExec('zLexCount', [$key, $min, $max]);
    }

    /**
     * ZREMRANGEBYLEX key min max
     *
     * 移除有序集合中给定的字典区间的所有 member
     * min max 语法:
     * '+' 和 '-' 表示正负无穷
     * '(' 和 '[' 表示开闭区间 '[a', '(c' 表示 'a' <= X < 'c'
     *
     * @param string $key
     * @param string $min
     * @param string $max
     *
     * @return int 被移除的元素数量
     */
    public function zRemRangeByLex($key, $min, $max)
    {
        return $this->_modeIns->masterExec('zRemRangeByLex', [$key, $min, $max]);
    }

    /**
     * ZSCAN key cursor [MATCH pattern] [COUNT count]
     *
     * 增量迭代有序集合中的 member 与 score
     *
     * pattern: 查找所有符合给定模式 pattern 的 key
     * pattern * 匹配数据库中所有 key
     * pattern h?llo 匹配 hello ,  hallo 和 hxllo 等
     * pattern h*llo 匹配 hllo 和 heeeeello 等
     * pattern h[ae]llo 匹配 hello 和 hallo , 但不匹配 hillo
     *
     * @param string $key
     * @param int    &$cursor 游标必须传, Redis 并发处理迭代时的状态记录, 客户端不需要关心这个参数
     * @param string $pattern
     * @param int    $count   每次迭代返回的记录限制, 默认为 10, 每次返回的数量不一定相同
     *
     * @return array|false
     */
    public function zScan($key, &$cursor, $pattern = '', $count = 10)
    {
        return $this->_modeIns->slaveExec('zScan', [$key, &$cursor, $pattern, $count]);
    }

    /**
     * ZUNIONSTORE destination keys [WEIGHTS weight [weight …]] [AGGREGATE SUM|MIN|MAX]
     *
     * 计算给定的一个或多个有序集的并集，并存储在 destination
     * WEIGHTS
     * 针对每个 zset(数据源) 计算 score 值时设置的系数, 默认 = 1
     * AGGREGATE
     * 默认情况下，结果集中 member 的 score 值是所有给定集下 score 之和(SUM)
     * 可选 'SUM', 'MIN', 'MAX'
     *
     * 参数举例: ('new', ['zset1', 'zset2'], [5, 0.5]])
     * 则表示: zset1 的 score 值乘以 5, zset2 的 score 值乘以 0.5
     *
     * @param string $destination 用于存储结果的新有序集 key
     * @param array  $keys        要进行聚合的有序集
     * @param array  $weights     对应 keys 的权重
     * @param string $aggregate   聚合类型, 默认为 'SUM' 可选值: 'SUM', 'MIN', 'MAX'
     *
     * @return int 返回新集合中的 member 数量
     */
    public function zUnionStore($destination, array $keys, array $weights = [], $aggregate = 'SUM')
    {
        return $this->_modeIns->masterExec('zUnionStore', [$destination, $keys, $weights, $aggregate]);
    }

    /**
     * ZINTERSTORE destination keys [WEIGHTS weight [weight …]] [AGGREGATE SUM|MIN|MAX]
     *
     * 计算给定的一个或多个有序集的交集，并存储在 destination
     * WEIGHTS
     * 针对每个 zset(数据源) 计算 score 值时设置的系数, 默认 = 1
     * AGGREGATE
     * 默认情况下，结果集中 member 的 score 值是所有给定集下 score 之和(SUM)
     * 可选 'SUM', 'MIN', 'MAX'
     *
     * 参数举例: ('new', ['zset1', 'zset2'], [5, 0.5]])
     * 则表示: zset1 的 score 值乘以 5, zset2 的 score 值乘以 0.5
     *
     * @param string $destination 用于存储结果的新有序集 key
     * @param array  $keys        要进行聚合的有序集
     * @param array  $weights     对应 keys 的权重
     * @param string $aggregate   聚合类型, 默认为 'SUM' 可选值: 'SUM', 'MIN', 'MAX'
     *
     * @return int 返回新集合中的 member 数量
     */
    public function zInterStore($destination, array $keys, array $weights = [], $aggregate = 'SUM')
    {
        return $this->_modeIns->masterExec('zInterStore', [$destination, $keys, $weights, $aggregate]);
    }
}
