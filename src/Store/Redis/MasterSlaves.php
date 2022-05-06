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

class MasterSlaves extends ModeAbstract
{
    protected $_masterConf;
    protected $_slaveConf;

    protected $_onlyMaster;

    protected $_master;
    protected $_slave;

    public function __construct(Conf $conf)
    {
        $this->initByConf($conf);
    }

    protected function initByConf(Conf $conf): void
    {
        $this->_master     = null;
        $this->_slave      = null;
        $this->_masterConf = $conf->getMaster();
        $slaves            = $conf->getSlaves();
        if ($conf->getOnlyMaster()) {
            $this->_onlyMaster = true;
        } else {
            $slaves = $conf->getSlaves();
            if (empty($slaves)) {
                $this->_onlyMaster = true;
            } elseif (count($slaves) > 1) {
                $this->_slaveConf = $slaves[array_rand($slaves)];
            } else {
                $this->_slaveConf = $slaves[0];
            }
        }
    }

    /**
     * 主库实例
     *
     * @return Client
     */
    public function getMaster(): Client
    {
        if (!$this->_master) {
            $this->_master = new Client($this->_masterConf);
        }

        return $this->_master;
    }

    /**
     * 从库实例 (没有从或onlyMaster时返回主库)
     *
     * @return Client
     */
    public function getSlave(): Client
    {
        if ($this->_onlyMaster) {
            return $this->getMaster();
        }
        if (!$this->_slave) {
            $this->_slave = new Client($this->_slaveConf);
        }

        return $this->_slave;
    }
}
