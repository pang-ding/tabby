<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Cache;

use Tabby\Test\TestCase;
use Tabby\Store\Redis\Conf;
use Tabby\Store\Redis\Redis;
use Tabby\Middleware\Cache\CacheRedis;

class CacheRedis_Test extends TestCase
{
    /**
     * @var CacheRedis
     */
    public static $cache;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        $conf = Conf::sentinel(
            'mymaster',
            [
                Conf::helper('127.0.0.1', 26380),
                Conf::helper('127.0.0.1', 26381),
                Conf::helper('127.0.0.1', 26382),
            ],
            Conf::helper()
        );
        $redis         = new Redis($conf);
        static::$cache = new CacheRedis($redis);
    }

    public function test_get_set()
    {
        $this->assertSame(static::$cache->set('test_get_set', 'a1'), true);
        $this->assertSame(static::$cache->get('test_get_set'), 'a1');

        $this->assertSame(static::$cache->set('test_get_set', 'a2', 1), true);
        $this->assertSame(static::$cache->get('test_get_set'), 'a2');
        usleep(1100000);
        $this->assertSame(static::$cache->get('test_get_set', 'def'), 'def');
    }

    public function test_delete()
    {
        $this->assertSame(static::$cache->set('test_delete', 'a1'), true);
        $this->assertSame(static::$cache->get('test_delete'), 'a1');
        $this->assertSame(static::$cache->delete('test_delete'), 1);
        $this->assertSame(static::$cache->delete('test_delete'), 0);
        $this->assertSame(static::$cache->get('test_get_set'), null);
        $this->assertSame(static::$cache->get('test_get_set', 'def'), 'def');
    }

    public function test_clear()
    {
        $this->assertSame(static::$cache->set('test_clear1', 'a1'), true);
        $this->assertSame(static::$cache->set('test_clear2', 'a2'), true);
        $this->assertSame(static::$cache->set('test_clear3', 'a3'), true);
        $this->assertSame(static::$cache->get('test_clear1'), 'a1');
        $this->assertSame(static::$cache->get('test_clear2'), 'a2');
        $this->assertSame(static::$cache->get('test_clear3'), 'a3');
        $this->assertSame(static::$cache->clear(), true);
        $this->assertSame(static::$cache->get('test_clear1'), null);
        $this->assertSame(static::$cache->get('test_clear2'), null);
        $this->assertSame(static::$cache->get('test_clear3'), null);
    }

    public function test_getMultiple()
    {
        $this->assertSame(static::$cache->set('test_getMultiple1', 'a1'), true);
        $this->assertSame(static::$cache->set('test_getMultiple2', 'a2'), true);
        $this->assertSame(static::$cache->set('test_getMultiple3', 'a3'), true);
        $this->assertSame(static::$cache->getMultiple(
            ['test_getMultiple1', 'test_getMultiple2', 'test_getMultiple3'],
        ), ['test_getMultiple1' => 'a1', 'test_getMultiple2' => 'a2', 'test_getMultiple3' => 'a3']);
        $this->assertSame(static::$cache->getMultiple(
            ['test_getMultiple1', 'test_getMultiple_none', 'test_getMultiple2', 'test_getMultiple_none2', 'test_getMultiple3'],
            'def'
        ), ['test_getMultiple1' => 'a1', 'test_getMultiple_none' => 'def', 'test_getMultiple2' => 'a2', 'test_getMultiple_none2' => 'def', 'test_getMultiple3' => 'a3']);
    }

    public function test_setMultiple()
    {
        $this->assertSame(static::$cache->setMultiple(['test_setMultiple1' => 'a1', 'test_setMultiple2' => 'a2']), true);
        $this->assertSame(static::$cache->get('test_setMultiple1'), 'a1');
        $this->assertSame(static::$cache->get('test_setMultiple2'), 'a2');
        $this->assertSame(static::$cache->setMultiple(['test_setMultiple3' => 'a3', 'test_setMultiple4' => 'a4'], 1), true);
        $this->assertSame(static::$cache->get('test_setMultiple3'), 'a3');
        $this->assertSame(static::$cache->get('test_setMultiple4'), 'a4');
        usleep(1100000);
        $this->assertSame(
            static::$cache->getMultiple(['test_setMultiple3', 'test_setMultiple4'], 'def'),
            [
                'test_setMultiple3' => 'def',
                'test_setMultiple4' => 'def',
            ]
        );
    }

    public function test_deleteMultiple()
    {
        $this->assertSame(static::$cache->set('test_deleteMultiple1', 'a1'), true);
        $this->assertSame(static::$cache->set('test_deleteMultiple2', 'a2'), true);
        $this->assertSame(static::$cache->set('test_deleteMultiple3', 'a3'), true);
        $this->assertSame(static::$cache->get('test_deleteMultiple1'), 'a1');
        $this->assertSame(static::$cache->get('test_deleteMultiple2'), 'a2');
        $this->assertSame(static::$cache->get('test_deleteMultiple3'), 'a3');
        $this->assertSame(static::$cache->deleteMultiple([
            'test_deleteMultiple1',
            'test_deleteMultiple2',
            'test_deleteMultiple3',
        ]), 3);
        $this->assertSame(static::$cache->get('test_deleteMultiple1', 'def'), 'def');
        $this->assertSame(static::$cache->get('test_deleteMultiple2', 'def'), 'def');
        $this->assertSame(static::$cache->get('test_deleteMultiple3', 'def'), 'def');
    }

    public function test_has()
    {
        $this->assertSame(static::$cache->set('test_has1', 'a1'), true);
        $this->assertSame(static::$cache->has('test_has1'), true);
        $this->assertSame(static::$cache->has('test_has_none'), false);
    }
}
