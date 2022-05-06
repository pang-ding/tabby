<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Middleware\Lock;

use Tabby\Middleware\Lock\Lock;
use Tabby\Store\Memcache\Memcache;
use Tabby\Test\TestCase;

class Lock_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
    }

    public function test_file()
    {
        $mem = new Memcache([['127.0.0.1', 11211]], []);
        //$lock = new Lock(Lock::DRIVER_FILE, 'test', 5, []);
        $lock = new Lock(Lock::DRIVER_MEMCACHE, 'test', 5, ['memcache' => $mem]);
        if ($lock->lock()) {
            print_r("====================================\nLocked\n");
            for ($i = 1; $i < 15; $i++) {
                sleep(1);
                print_r('checkOwner:' . ($lock->checkOwner() ? 'TRUE' : 'FALSE') . "\n");
                ob_flush();
            }
            $lock->unlock();
        } else {
            print_r("====================================\nUnable lock\n");
        }
        exit;
    }
}
