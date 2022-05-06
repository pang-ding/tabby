<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Store\Memcache;

use Tabby\Store\Memcache\Memcache;
use Tabby\Test\TestCase;

class Memcache_Test extends TestCase
{
    private static $mem;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        static::$mem = new Memcache([['127.0.0.1', 11211]], []);
    }

    public function test_set_get()
    {
        $this->assertTrue(static::$mem->set('foo', 'bar'));
        $this->assertSame(static::$mem->get('foo'), 'bar');
    }
}
