<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Lock;

use Tabby\Error\ErrorSys;
use Tabby\Middleware\Lock\Drivers\DriverAbstract;
use Tabby\Middleware\Lock\Drivers\DriverFile;
use Tabby\Middleware\Lock\Drivers\DriverMemcache;

final class Lock
{
    const DRIVER_FILE     = 'file';
    const DRIVER_APCU     = 'apcu';
    const DRIVER_REDIS    = 'redis';
    const DRIVER_MEMCACHE = 'memcache';

    const MAX_TTL = 86400;

    /**
     * @var DriverAbstract
     */
    private $_driver;

    /**
     *
     *
     * @param
     */
    public function __construct(string $driverName, string $lockName, int $ttl = 30, array $driverAgrs = [], bool $checkTimeout = true)
    {
        switch ($driverName) {
            case self::DRIVER_FILE:
                $this->_driver = new DriverFile($lockName, $ttl, $checkTimeout, $driverAgrs);

                break;
            case self::DRIVER_MEMCACHE:
                $this->_driver = new DriverMemcache($lockName, $ttl, $checkTimeout, $driverAgrs);

                break;
            default:
                throw new ErrorSys('Lock Error: Unknown $driverName');
        }
    }

    /**
     * 加锁
     *
     * @param
     */
    public function lock(): bool
    {
        return $this->_driver->lock();
    }

    public function unlock(): bool
    {
        return $this->_driver->unlock();
    }

    public function checkOwner(): bool
    {
        return $this->_driver->checkOwner();
    }
}
