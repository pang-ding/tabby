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

abstract class ModeAbstract
{
    protected $_onlyMaster;

    abstract public function __construct(Conf $conf);

    /**
     * 主库执行
     *
     * @param string $cmd
     * @param array  $args
     *
     * @return mixed
     */
    public function masterExec(string $cmd, array $args = [])
    {
        return $this->getMaster()->phpRedis->$cmd(...$args);
    }

    /**
     * 从库执行
     *
     * @param string $cmd
     * @param array  $args
     *
     * @return mixed
     */
    public function slaveExec(string $cmd, array $args = [])
    {
        return $this->getSlave()->phpRedis->$cmd(...$args);
    }

    abstract public function getMaster();

    abstract public function getSlave();
}
