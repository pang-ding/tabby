<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Lock\Drivers;

use Tabby\Error\ErrorSys;
use Tabby\Tabby;

abstract class DriverAbstract
{
    protected static $_MaxTTL = 86400;

    protected $_lockName;
    protected $_ttl;
    protected $_pid;
    protected $_checkTimeout;

    public function __construct(string $lockName, int $ttl, bool $checkTimeout, array $driverAgrs)
    {
        // 没有对长度进行限制, 注释里做说明
        if ($lockName === '') {
            throw new ErrorSys('Lock Error: $lockName cannot be empty');
        }
        if ($ttl < 1 || $ttl > static::$_MaxTTL) {
            throw new ErrorSys('Lock Error: $ttl must between 1 - ' . static::$_MaxTTL);
        }
        $this->_lockName     = $lockName;
        $this->_ttl          = $ttl;
        $this->_checkTimeout = $checkTimeout;
        $this->_pid          = posix_getpid();

        $this->init($driverAgrs);
    }

    abstract protected function init(array $driverAgrs): void;

    abstract public function lock(): bool;

    abstract public function unlock(): bool;

    abstract public function checkOwner(): bool;
}
