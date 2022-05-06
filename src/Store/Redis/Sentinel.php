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
use Tabby\Middleware\Lock\Lock;
use Tabby\Tabby;
use Tabby\Utils\StrUtils;

class Sentinel extends MasterSlaves
{
    const CACHE_FILE = 'redis.conf.php';
    protected $_cacheFile;
    protected $_sentinelConf;
    protected $_sentinel;

    public function __construct(Conf $conf)
    {
        $this->_cacheFile    = StrUtils::dirLastSeparator(Tabby::$Conf['tabby']['tmpPath']) . self::CACHE_FILE;
        $this->_sentinelConf = $conf;
        parent::__construct($this->getMasterAndSlavesConf());
    }

    /**
     * 主库实例
     * 连接失败时刷配置缓存 ,尝试重新连接
     *
     * @return Client
     */
    public function getMaster(): Client
    {
        try {
            $master = parent::getMaster();

            return $master;
        } catch (\Throwable $e) {
            Tabby::$Log->warning('RedisSentinel master connect failed');

            parent::initByConf($this->flushConf());

            return parent::getMaster();
        }
    }

    /**
     * 从库实例 (没有从或onlyMaster时返回主库)
     * 连接失败时刷配置缓存 ,尝试重新连接
     *
     * @return Client
     */
    public function getSlave(): Client
    {
        try {
            return parent::getSlave();
        } catch (\Throwable $e) {
            Tabby::$Log->warning('RedisSentinel sleve connect failed');

            parent::initByConf($this->flushConf());

            return parent::getSlave();
        }
    }

    /**
     * 从哨兵获取配置并刷新缓存 (抢锁, 没抢到不刷新)
     *
     * @return Conf
     */
    public function flushConf(): Conf
    {
        Tabby::$Log->info('RedisSentinel load config from sentinel');
        $lock = $this->_sentinelConf->getLock();
        if (!$lock instanceof Lock) {
            $lock = new Lock(Lock::DRIVER_FILE, 'redis_sentinel_flush', 5, []);
        }
        $isLocked = $lock->lock();
        $conf     = $this->getMasterAndSlavesConfBySentinel();
        if (!is_array($conf) || empty($conf['master'])) {
            if ($isLocked) {
                $lock->unlock();
            }

            throw new ErrorSys('RedisSentinel Error: Get config by sentinel failed');
        }
        if ($isLocked) {
            $data = "<?php\nreturn " . var_export($conf, true) . ';';
            if (file_put_contents($this->_cacheFile, $data)) {
                opcache_invalidate($this->_cacheFile, true);
                Tabby::$Log->warning("RedisSentinel flush config file SUCCESS [master {$conf['master']['host']}:{$conf['master']['port']}] ");
            } else {
                Tabby::$Log->error("RedisSentinel flush config file FAILED [master {$conf['master']['host']}:{$conf['master']['port']}] ");
            }
            $lock->unlock();
        }

        return Conf::masterSlave($conf['master'], $conf['slaves']);
    }

    /**
     * 主库执行 (重写)
     * 监控返回异常, 遇到 READONLY... 判定已经切主, 刷新配置
     *
     * @param string $cmd
     * @param array  $args
     *
     * @return mixed
     */
    public function masterExec($cmd, array $args = [])
    {
        try {
            return $this->getMaster()->phpRedis->$cmd(...$args);
        } catch (\RedisException $e) {
            if (substr($e->getMessage(), 0, 8) === 'READONLY') {
                parent::initByConf($this->flushConf());

                return $this->getMaster()->phpRedis->$cmd(...$args);
            } else {
                throw new $e;
            }
        }
    }

    /**
     * 尝试从缓存读取主从配置 失败时调用 flushConf 刷新配置并返回结果
     *
     * @return Conf
     */
    protected function getMasterAndSlavesConf(): Conf
    {
        if (file_exists($this->_cacheFile)) {
            $confArray = include $this->_cacheFile;
            if (is_array($confArray) && !empty($confArray['master'])) {
                Tabby::$Log->debug('RedisSentinel cache hit');

                return Conf::masterSlave($confArray['master'], $confArray['slaves']);
            }
        }

        return $this->flushConf();
    }

    /**
     * 从哨兵获得主从配置
     *
     * @return array|false
     */
    protected function getMasterAndSlavesConfBySentinel()
    {
        $sentsConf = $this->_sentinelConf->getSentinels();
        if (!shuffle($sentsConf)) {
            throw new ErrorSys(
                'Redis Error: Sentinels config must be a array (' . var_export($sentsConf, true) . ')'
            );
        }

        foreach ($sentsConf as $sConf) {
            try {
                $this->_sentinel = new \RedisSentinel(
                    $sConf['host'],
                    $sConf['port'],
                    $sConf['connectTimeout'],
                    $sConf['persistent'],
                    $sConf['retryInterval'],
                    $sConf['readTimeout']
                );
                $sockMaster = $this->_sentinel->master($this->_sentinelConf->getMasterName());
                if (!is_array($sockMaster) || empty($sockMaster['ip']) || empty($sockMaster['port'])) {
                    Tabby::$Log->warning(
                        'RedisSentinel Error: Sentinel get config failed [result ' .
                        var_export($sockMaster, true) . "] [sentinel {$sConf['host']}:{$sConf['port']}]"
                    );

                    continue;
                }
                $sockSlaves = $this->_sentinel->slaves($this->_sentinelConf->getMasterName());
                if (!is_array($sockSlaves)) {
                    $sockSlaves = [];
                }
                $master         = $this->_sentinelConf->getMaster();
                $master['host'] = $sockMaster['ip'];
                $master['port'] = $sockMaster['port'];
                $slaves         = [];
                foreach ($sockSlaves as $slv) {
                    if (is_array($slv) && !empty($slv['ip']) && !empty($slv['port'])) {
                        $slaveConf         = $this->_sentinelConf->getMaster();
                        $slaveConf['host'] = $slv['ip'];
                        $slaveConf['port'] = $slv['port'];
                        $slaves[]          = $slaveConf;
                    }
                }

                return ['master' => $master, 'slaves' => $slaves];
            } catch (\Throwable $e) {
                Tabby::$Log->warning(
                    "RedisSentinel Error: Sentinel get config failed [sentinel {$sConf['host']}:{$sConf['port']}]"
                );
            }
        }

        return false;
    }

    // protected function getMasterAndSlavesConfBySentinel(): Conf
    // {
    //     function formatResult($rst)
    //     {
    //         if (!is_array($rst)) {
    //             return false;
    //         }
    //         $r    = [];
    //         $flag = false;
    //         do {
    //             switch (current($rst)) {
    //                 case 'ip':
    //                     $r[0] = next($rst);
    //                     if ($flag) {break 2;}
    //                     $flag = true;
    //                     break;
    //                 case 'port':
    //                     $r[1] = next($rst);
    //                     if ($flag) {break 2;}
    //                     $flag = true;
    //                     break;
    //                 default:
    //                     next($rst);
    //             }
    //         } while (next($rst));
    //         return count($r) === 2 ? $r : false;
    //     }
    //     $sentsConf = $this->_sentinelConf->getSentinels();
    //     if (!shuffle($sentsConf)) {
    //         throw new ErrorSys(
    //             'Redis Error: Sentinels config must be a array (' . var_export($sentsConf, true) . ')'
    //         );
    //     }

    //     foreach ($sentsConf as $sConf) {
    //         try {
    //             $this->_sentinel = (new Client($sConf))->phpRedis;
    //             $sockMaster      = formatResult(
    //                 $this->_sentinel->rawCommand('SENTINEL', 'master', $this->_sentinelConf->getMasterName())
    //             );
    //             if (!$sockMaster) {
    //                 continue;
    //             }
    //             $sockSlaves = $this->_sentinel->rawCommand('SENTINEL', 'slaves', $this->_sentinelConf->getMasterName());
    //             if (!is_array($sockSlaves)) {
    //                 continue;
    //             }
    //             $master         = $this->_sentinelConf->getMaster();
    //             $master['host'] = $sockMaster[0];
    //             $master['port'] = $sockMaster[1];
    //             $slaves         = [];
    //             foreach ($sockSlaves as $slv) {
    //                 $slv = formatResult($slv);
    //                 if ($slv) {
    //                     $slaveConf         = $this->_sentinelConf->getMaster();
    //                     $slaveConf['host'] = $slv[0];
    //                     $slaveConf['port'] = $slv[1];
    //                     $slaves[]          = $slaveConf;
    //                 }
    //             }
    //             return Conf::masterSlave($master, $slaves);
    //         } catch (\Throwable $e) {
    //         }
    //     }

    //     return $conf;
    // }
}
