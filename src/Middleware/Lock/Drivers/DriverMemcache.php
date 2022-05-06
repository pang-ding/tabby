<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
use Tabby\Store\Memcache\Memcache;

final class DriverMemcache extends DriverAbstract
{
    private $_key;
    private $_memcache;

    protected function init(array $driverAgrs): void
    {
        if ($driverAgrs['memcache'] instanceof Memcache) {
            $this->_memcache = $driverAgrs['memcache'];
        } else {
            throw new ErrorSys('Lock Error: $memcache must be instance of Memcache');
        }
        $this->_key = 'simple_lock_' . $this->_lockName;
    }

    public function lock(): bool
    {
        return $this->_memcache->add($this->_key, $this->_pid, $this->_ttl);
    }

    public function unlock(): bool
    {
        return $this->_memcache->delete($this->_key);
    }

    public function checkOwner(): bool
    {
        return ((int) $this->_memcache->get($this->_key)) === $this->_pid;
    }
}
