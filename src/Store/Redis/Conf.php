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

use Tabby\Middleware\Lock\Lock;

class Conf
{
    const MODE_SINGLE       = 'single';       // 单实例 或 各种运维搞的高级货
    const MODE_MASTER_SLAVE = 'master_slave'; // 主从
    const MODE_SENTINEL     = 'sentinel';     // 哨兵

    /**
     * 模式
     *
     * @var string
     */
    protected $_mode;
    protected $_master;
    protected $_slaves = [];
    protected $_sentinels;
    protected $_lock;
    protected $_masterName;
    protected $_onlyMaster;

    protected function __construct($mode, $master, $slaves, $sentinels, $masterName, $onlyMaster, $lock)
    {
        $this->_mode       = $mode;
        $this->_master     = $master;
        $this->_slaves     = $slaves;
        $this->_sentinels  = $sentinels;
        $this->_masterName = $masterName;
        $this->_onlyMaster = $onlyMaster;
        $this->_lock       = $lock;
    }

    /**
     * 连接配置生成器
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
    public static function helper(
        string $host = '',
        int $port = 6379,
        float $connectTimeout = 0,
        int $retryInterval = 0,
        float $readTimeout = 0,
        $persistent = false,
        $auth = null,
        array $ssl = null
    ): array {
        return [
            'host'           => $host,
            'port'           => $port,
            'connectTimeout' => $connectTimeout,
            'retryInterval'  => $retryInterval,
            'readTimeout'    => $readTimeout,
            'persistent'     => $persistent,
            'auth'           => $auth,
            'ssl'            => $ssl,
        ];
    }

    /**
     * 单实例模式配置生成器
     *
     * @param array $master
     *
     * @return Conf
     */
    public static function single(array $master): Conf
    {
        return new Conf(Conf::MODE_SINGLE, $master, null, null, null, true, null);
    }

    /**
     * 主从模式配置生成器
     *
     * @param array $master
     * @param array $slaves
     * @param bool  $onlyMaster
     *
     * @return Conf
     */
    public static function masterSlave(array $master, array $slaves, bool $onlyMaster = false): Conf
    {
        return new Conf(Conf::MODE_MASTER_SLAVE, $master, $slaves, null, null, $onlyMaster, null);
    }

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
    public static function sentinel(string $masterName, array $sentinels, array $clientsConf, ?Lock $lock = null, bool $onlyMaster = false): Conf
    {
        return new Conf(Conf::MODE_SENTINEL, $clientsConf, null, $sentinels, $masterName, $onlyMaster, $lock);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->_mode;
    }

    /**
     * Get the value of _sentinels
     */
    public function getSentinels()
    {
        return $this->_sentinels;
    }

    /**
     * Get the value of _slaves
     */
    public function getSlaves()
    {
        return $this->_slaves;
    }

    /**
     * Get the value of _master
     */
    public function getMaster()
    {
        return $this->_master;
    }

    /**
     * Get the value of _masterName
     */
    public function getMasterName()
    {
        return $this->_masterName;
    }

    public function getOnlyMaster()
    {
        return $this->_onlyMaster;
    }

    public function getLock()
    {
        return $this->_lock;
    }
}
