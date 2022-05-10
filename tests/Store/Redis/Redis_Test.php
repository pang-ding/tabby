<?php
/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Store\Redis;

use Tabby\Store\Redis\Conf;
use Tabby\Store\Redis\Redis;
use Tabby\Test\TestCase;

class Redis_Test extends TestCase
{
    public static $conf;

    /**
     * @var Redis
     */
    public static $redis;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        self::$conf = Conf::sentinel(
            'mymaster',
            [
                Conf::helper('127.0.0.1', 26380),
                Conf::helper('127.0.0.1', 26381),
                Conf::helper('127.0.0.1', 26382),
            ],
            Conf::helper(),
            null,
            true
        );
        self::$redis = new Redis(self::$conf);
        //self::$redis->select(6);
        self::$redis->flushDb(false);

        // scan返回空记录自动重试
        //self::$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
    }

    public function test_scan()
    {
        $i = 1;
        for ($x = 1; $x <= 10; $x++) {
            $v = [];
            for ($y = 1; $y <= 10; $y++) {
                $v['V' . $i] = 'V' . $i;
                $i++;
            }
            if (!self::$redis->mSet($v)) {
                $this->assertTrue(false);
            }
        }
        $iterator = null;

        $rst = [];
        while ($kv = self::$redis->scan($iterator, '*', 100)) {
            foreach ($kv as $v) {
                $rst[$v] = $v;
            }
        }
        $this->assertSame(count($rst), 100);
    }

    public function test_hScan()
    {
        $i = 1;
        for ($x = 1; $x <= 10; $x++) {
            $v = [];
            for ($y = 1; $y <= 10; $y++) {
                $v['V' . $i] = 'V' . $i;
                $i++;
            }
            if (!self::$redis->hMSet('test_hScan', $v)) {
                $this->assertTrue(false);
            }
        }
        $iterator = null;

        $rst = [];
        while ($kv = self::$redis->hScan('test_hScan', $iterator, '*', 100)) {
            foreach ($kv as $k => $v) {
                $rst[$k] = $v;
                $this->assertSame($k, $v);
            }
        }
        $this->assertSame(count($rst), 100);
    }

    //zScan
    public function test_zScan()
    {
        for ($x = 1; $x <= 100; $x++) {
            if (!self::$redis->zAdd('test_zScan', $x, 'value' . $x)) {
                $this->assertTrue(false);
            }
        }
        $iterator = null;

        $rst = [];
        while ($kv = self::$redis->zScan('test_zScan', $iterator, '*', 100)) {
            foreach ($kv as $k => $v) {
                $rst[$k] = $v;
            }
        }
        $this->assertSame(count($rst), 100);
    }

    public function test_set_get()
    {
        $this->assertTrue(
            self::$redis->set('test_set_get', 'value1')
        );
        $this->assertSame(self::$redis->get('test_set_get'), 'value1');
        // ttl:1
        $this->assertTrue(
            self::$redis->set('test_set_get', 'value2', 1)
        );
        $this->assertSame(self::$redis->get('test_set_get'), 'value2');
        usleep(1100000);
        $this->assertSame(self::$redis->get('test_set_get'), false);
        // nx
        self::$redis->set('test_set_get', 'value4');
        $this->assertFalse(
            self::$redis->set('test_set_get', 'value3', ['nx'])
        );
        // nx
        $this->assertTrue(
            self::$redis->set('test_set_get_nx', 'value_nx_1', ['nx'])
        );
        $this->assertSame(self::$redis->get('test_set_get_nx'), 'value_nx_1');

        self::$redis->hSet('test_ha', 'test_a', 'a');
    }

    public function test_setEx()
    {
        $this->assertTrue(
            self::$redis->setEx('test_set_setEx', 1, 'value1')
        );
        $this->assertSame(self::$redis->get('test_set_setEx'), 'value1');
        usleep(2200000);
        $this->assertSame(self::$redis->get('test_set_setEx'), false);
    }

    public function test_psetEx()
    {
        $this->assertTrue(
            self::$redis->psetEx('test_set_psetEx', 100, 'value2')
        );
        $this->assertSame(self::$redis->get('test_set_psetEx'), 'value2');
        usleep(200000);
        $this->assertSame(self::$redis->get('test_set_psetEx'), false);
    }

    public function test_setNx()
    {
        $this->assertTrue(
            self::$redis->setNx('test_set_setNx', 'value1')
        );
        $this->assertFalse(
            self::$redis->setNx('test_set_setNx', 'value2')
        );
        $this->assertSame(self::$redis->get('test_set_setNx'), 'value1');
    }

    // 以下来自Google Copilot
    //test_getSet
    public function test_getSet()
    {
        $this->assertTrue(
            self::$redis->set('test_getSet', 'value1')
        );
        $this->assertSame(self::$redis->get('test_getSet'), 'value1');
        $this->assertSame(self::$redis->getSet('test_getSet', 'value2'), 'value1');
        $this->assertSame(self::$redis->get('test_getSet'), 'value2');
    }

    //strlen
    public function test_strlen()
    {
        $this->assertTrue(
            self::$redis->set('test_strlen', 'value1')
        );
        $this->assertSame(self::$redis->strlen('test_strlen'), 6);
    }

    //test_append
    public function test_append()
    {
        $this->assertTrue(
            self::$redis->set('test_append', 'value1')
        );
        $this->assertSame(self::$redis->append('test_append', 'value2'), 12);
        $this->assertSame(self::$redis->get('test_append'), 'value1value2');
    }

    //test_incr
    public function test_incr()
    {
        $this->assertTrue(
            self::$redis->set('test_incr', 10)
        );
        $this->assertSame(self::$redis->incr('test_incr'), 11);
        $this->assertSame(self::$redis->get('test_incr'), '11');
    }

    //test_incrBy
    public function test_incrBy()
    {
        $this->assertTrue(
            self::$redis->set('test_incrBy', 10)
        );
        $this->assertSame(self::$redis->incrBy('test_incrBy', 5), 15);
        $this->assertSame(self::$redis->get('test_incrBy'), '15');
    }

    //test_decr
    public function test_decr()
    {
        $this->assertTrue(
            self::$redis->set('test_decr', 10)
        );
        $this->assertSame(self::$redis->decr('test_decr'), 9);
        $this->assertSame(self::$redis->get('test_decr'), '9');
    }

    //test_decrBy
    public function test_decrBy()
    {
        $this->assertTrue(
            self::$redis->set('test_decrBy', 10)
        );
        $this->assertSame(self::$redis->decrBy('test_decrBy', 5), 5);
        $this->assertSame(self::$redis->get('test_decrBy'), '5');
    }

    //test_mget
    public function test_mget()
    {
        $this->assertTrue(
            self::$redis->set('test_mget_1', 'value1')
        );
        $this->assertTrue(
            self::$redis->set('test_mget_2', 'value2')
        );
        $this->assertSame(self::$redis->mget(['test_mget_1', 'test_mget_2']), ['value1', 'value2']);
    }

    //test_mset
    public function test_mset()
    {
        $this->assertTrue(
            self::$redis->mset(['test_mset_1' => 'value1', 'test_mset_2' => 'value2'])
        );
        $this->assertSame(self::$redis->get('test_mset_1'), 'value1');
        $this->assertSame(self::$redis->get('test_mset_2'), 'value2');
    }

    //test_msetnx
    public function test_msetnx()
    {
        $this->assertTrue(
            self::$redis->msetnx(['test_msetnx_1' => 'value1', 'test_msetnx_2' => 'value2'])
        );
        $this->assertSame(self::$redis->get('test_msetnx_1'), 'value1');
        $this->assertSame(self::$redis->get('test_msetnx_2'), 'value2');
        $this->assertFalse(
            self::$redis->msetnx(['test_msetnx_1' => 'value1', 'test_msetnx_2' => 'value2'])
        );
        $this->assertSame(self::$redis->get('test_msetnx_1'), 'value1');
        $this->assertSame(self::$redis->get('test_msetnx_2'), 'value2');
    }

    //test_setex
    public function test_setex2()
    {
        $this->assertTrue(
            self::$redis->setex('test_setex', 1, 'value1')
        );
        $this->assertSame(self::$redis->get('test_setex'), 'value1');
        usleep(1200000);
        $this->assertSame(self::$redis->get('test_setex'), false);
    }

    //test_psetex
    public function test_psetex2()
    {
        $this->assertTrue(
            self::$redis->psetex('test_psetex', 100, 'value1')
        );
        $this->assertSame(self::$redis->get('test_psetex'), 'value1');
        usleep(150000);
        $this->assertSame(self::$redis->get('test_psetex'), false);
    }

    //test_setnx
    public function test_setnx2()
    {
        $this->assertTrue(
            self::$redis->setnx('test_setnx', 'value1')
        );
        $this->assertSame(self::$redis->get('test_setnx'), 'value1');
        $this->assertFalse(
            self::$redis->setnx('test_setnx', 'value2')
        );
        $this->assertSame(self::$redis->get('test_setnx'), 'value1');
    }

    //test_setrange
    public function test_setrange()
    {
        $this->assertTrue(
            self::$redis->set('test_setrange', 'value1')
        );
        $this->assertSame(self::$redis->setrange('test_setrange', 2, 'value2'), 8);
        $this->assertSame(self::$redis->get('test_setrange'), 'vavalue2');
    }

    //test_strlen
    public function test_strlen2()
    {
        $this->assertTrue(
            self::$redis->set('test_strlen', 'value1')
        );
        $this->assertSame(self::$redis->strlen('test_strlen'), 6);
    }

    //test_getRange
    public function test_getRange()
    {
        $this->assertTrue(
            self::$redis->set('test_getRange', 'value1')
        );
        $this->assertSame(self::$redis->getrange('test_getRange', 0, 1), 'va');
    }

    //test_exists
    public function test_exists()
    {
        $this->assertTrue(
            self::$redis->set('test_exists1', 'value1')
        );
        $this->assertTrue(
            self::$redis->set('test_exists2', 'value1')
        );
        $this->assertSame(self::$redis->exists('test_exists1', 'test_exists2', 'test_exists3'), 2);
        $this->assertSame(self::$redis->exists('test_exists_not_exists'), 0);
        $this->assertSame(self::$redis->exists(['test_exists1', 'test_exists2', 'test_exists3']), 2);
    }

    //test_setbit
    public function test_setbit()
    {
        self::$redis->set('test_setbit', 'a');
        $this->assertSame(self::$redis->setbit('test_setbit', 6, 1), 0);
        $this->assertSame(self::$redis->setbit('test_setbit', 7, 0), 1);
        $this->assertSame(self::$redis->get('test_setbit'), 'b');
    }

    //test_getbit
    public function test_getbit()
    {
        $this->assertTrue(
            self::$redis->set('test_getbit', 'a')
        );
        $this->assertSame(self::$redis->getbit('test_getbit', 6), 0);
        $this->assertSame(self::$redis->getbit('test_getbit', 7), 1);
    }

    //test_incrByFloat
    public function test_incrByFloat()
    {
        $this->assertTrue(
            self::$redis->set('test_incrByFloat', 1)
        );
        $this->assertSame(self::$redis->incrByFloat('test_incrByFloat', 1.1), 2.1);
    }

    //test_type
    public function test_type()
    {
        $this->assertTrue(
            self::$redis->set('test_type', 'value1')
        );
        $this->assertSame(self::$redis->type('test_type'), Redis::REDIS_STRING);
        $this->assertSame(self::$redis->type('test_type_not_exists'), Redis::REDIS_NOT_FOUND);
    }

    //test_rename
    public function test_rename()
    {
        $this->assertTrue(
            self::$redis->set('test_rename', 'value1')
        );
        $this->assertTrue(self::$redis->rename('test_rename', 'test_rename_new'));
        $this->assertSame(self::$redis->get('test_rename_new'), 'value1');
        $this->assertFalse(self::$redis->rename('test_rename_not_exists', 'test_rename_new'));
    }

    //test_renameNx
    public function test_renameNx()
    {
        $this->assertTrue(
            self::$redis->set('test_renameNx', 'value1')
        );
        $this->assertTrue(
            self::$redis->set('test_renameNx_2', 'value1')
        );
        $this->assertTrue(self::$redis->renameNx('test_renameNx', 'test_renameNx_new'));
        $this->assertSame(self::$redis->get('test_renameNx_new'), 'value1');
        $this->assertFalse(self::$redis->renameNx('test_renameNx_not_2', 'test_renameNx_new'));
        $this->assertFalse(self::$redis->renameNx('test_renameNx_not_exists', 'test_renameNx_new_2'));
    }

    //test_del
    public function test_del()
    {
        self::$redis->set('test_del1', 'value1');
        self::$redis->set('test_del2', 'value1');
        self::$redis->set('test_del3', 'value1');
        $this->assertSame(self::$redis->del('test_del1', 'test_del2', 'test_del_0'), 2);
        $this->assertSame(self::$redis->del('test_del1', 'test_del2', 'test_del_0'), 0);
        $this->assertSame(self::$redis->exists('test_del2'), 0);
        $this->assertSame(self::$redis->exists('test_del1', 'test_del2', 'test_del3'), 1);
    }

    //test_unlink
    public function test_unlink()
    {
        self::$redis->set('test_unlink1', 'value1');
        self::$redis->set('test_unlink2', 'value1');
        self::$redis->set('test_unlink3', 'value1');
        $this->assertSame(self::$redis->unlink('test_unlink1', 'test_unlink2'), 2);
        $this->assertSame(self::$redis->unlink('test_unlink1', 'test_unlink2', 'test_unlink_0'), 0);
        $this->assertSame(self::$redis->exists('test_unlink2'), 0);
        $this->assertSame(self::$redis->exists('test_unlink1', 'test_unlink2', 'test_unlink3'), 1);
    }

    //test_randomKey
    public function test_randomKey()
    {
        $this->assertTrue(
            self::$redis->set('test_randomKey', 'value1')
        );
        $this->assertTrue(is_string(self::$redis->randomKey()));
    }

    //test_keys
    public function test_keys()
    {
        $this->assertTrue(
            self::$redis->set('test_keys1', 'value1')
        );
        $this->assertTrue(
            self::$redis->set('test_keys2', 'value1')
        );
        $this->assertTrue(
            self::$redis->set('test_keys3', 'value1')
        );
        // keys结果存入变量,进行排序
        $keys = self::$redis->keys('test_keys*');
        sort($keys);
        $this->assertSame($keys, ['test_keys1', 'test_keys2', 'test_keys3']);
        $this->assertSame(self::$redis->keys('test_keys*_not_exists'), []);
    }

    //test_sort
    public function test_sort()
    {
        self::$redis->sAdd('s', 5);
        self::$redis->sAdd('s', 4);
        self::$redis->sAdd('s', 2);
        self::$redis->sAdd('s', 1);
        self::$redis->sAdd('s', 3);
        $this->assertSame(self::$redis->sort('s', ['sort' => 'desc']), ['5', '4', '3', '2', '1']);
        //创建一个列表,并设置列表的值
        self::$redis->lPush('l', 'a', 'c', 'b');
        $this->assertSame(self::$redis->sort('l', ['sort' => 'asc', 'alpha' => true]), ['a', 'b', 'c']);
    }

    //test_expire
    public function test_expire()
    {
        $this->assertTrue(
            self::$redis->set('test_expire', 'value1')
        );
        $this->assertSame(self::$redis->ttl('test_expire'), -1);
        $this->assertTrue(self::$redis->expire('test_expire', 5));
        $this->assertSame(self::$redis->ttl('test_expire'), 5);
    }

    //test_expireAt
    public function test_expireAt()
    {
        $this->assertTrue(
            self::$redis->set('test_expireAt', 'value1')
        );
        $this->assertSame(self::$redis->ttl('test_expireAt'), -1);
        $this->assertTrue(self::$redis->expireAt('test_expireAt', time() + 5));
        $this->assertTrue(self::$redis->ttl('test_expireAt') >= 4);
    }

    //test_ttl
    public function test_ttl()
    {
        $this->assertTrue(
            self::$redis->set('test_ttl', 'value1')
        );
        $this->assertSame(self::$redis->ttl('test_ttl'), -1);
        $this->assertTrue(self::$redis->expire('test_ttl', 5));
        $this->assertSame(self::$redis->ttl('test_ttl'), 5);
        $this->assertSame(self::$redis->ttl('test_ttl_no'), -2);
    }

    //test_persist
    public function test_persist()
    {
        $this->assertTrue(
            self::$redis->set('test_persist', 'value1')
        );
        $this->assertTrue(self::$redis->expire('test_persist', 5));
        $this->assertSame(self::$redis->ttl('test_persist'), 5);
        $this->assertTrue(self::$redis->persist('test_persist'));
        $this->assertSame(self::$redis->ttl('test_persist'), -1);
    }

    //test_pexpire
    public function test_pexpire()
    {
        $this->assertTrue(
            self::$redis->set('test_pexpire', 'value1')
        );
        $this->assertSame(self::$redis->ttl('test_pexpire'), -1);
        $this->assertTrue(self::$redis->pexpire('test_pexpire', 500));
        $this->assertTrue(self::$redis->pttl('test_pexpire') > 400);
    }

    //test_pExpireAt
    public function test_pExpireAt()
    {
        $this->assertTrue(
            self::$redis->set('test_pExpireAt', 'value1')
        );
        $this->assertSame(self::$redis->ttl('test_pExpireAt'), -1);
        $this->assertTrue(self::$redis->pExpireAt('test_pExpireAt', time() * 1000 + 3000));
        $this->assertTrue(self::$redis->ttl('test_pExpireAt') > 1);
    }

    //test_hSet
    public function test_hSet()
    {
        $this->assertSame(
            self::$redis->hSet('test_hSet', 'key1', 'value1'),
            1
        );
        $this->assertSame(self::$redis->hGet('test_hSet', 'key1'), 'value1');
        $this->assertSame(
            self::$redis->hSet('test_hSet', 'key2', 'value2'),
            1
        );
        $this->assertSame(self::$redis->hGet('test_hSet', 'key2'), 'value2');
        $this->assertSame(
            self::$redis->hSet('test_hSet', 'key1', 'value3'),
            0
        );
        $this->assertSame(self::$redis->hGet('test_hSet', 'key1'), 'value3');
    }

    //test_hSetNx
    public function test_hSetNx()
    {
        $this->assertSame(
            self::$redis->hSetNx('test_hSetNx', 'key1', 'value1'),
            true
        );
        $this->assertSame(self::$redis->hGet('test_hSetNx', 'key1'), 'value1');
        $this->assertSame(
            self::$redis->hSetNx('test_hSetNx', 'key2', 'value2'),
            true
        );
        $this->assertSame(self::$redis->hGet('test_hSetNx', 'key2'), 'value2');
        $this->assertSame(
            self::$redis->hSetNx('test_hSetNx', 'key1', 'value3'),
            false
        );
        $this->assertSame(
            self::$redis->hSetNx('test_pExpireAt', 'xxx', 'xxx'),
            false
        );
        $this->assertSame(self::$redis->hGet('test_hSetNx', 'key1'), 'value1');
    }

    //test_hGet
    public function test_hGet()
    {
        $this->assertSame(
            self::$redis->hSet('test_hGet', 'key1', 'value1'),
            1
        );
        $this->assertSame(self::$redis->hGet('test_hGet', 'key1'), 'value1');
        $this->assertSame(
            self::$redis->hSet('test_hGet', 'key2', 'value2'),
            1
        );
        $this->assertSame(self::$redis->hGet('test_hGet', 'key2'), 'value2');
        $this->assertSame(
            self::$redis->hSet('test_hGet', 'key1', 'value3'),
            0
        );
        $this->assertSame(self::$redis->hGet('test_hGet', 'key1'), 'value3');
        $this->assertSame(self::$redis->hGet('test_hGet', 'key3'), false);
    }

    //test_hExists
    public function test_hExists()
    {
        $this->assertSame(
            self::$redis->hSet('test_hExists', 'key1', 'value1'),
            1
        );
        $this->assertSame(self::$redis->hExists('test_hExists', 'key1'), true);
        $this->assertSame(
            self::$redis->hSet('test_hExists', 'key2', 'value2'),
            1
        );
        $this->assertSame(self::$redis->hExists('test_hExists', 'key2'), true);
        $this->assertSame(
            self::$redis->hSet('test_hExists', 'key1', 'value3'),
            0
        );
        $this->assertSame(self::$redis->hExists('test_hExists', 'key1'), true);
        $this->assertSame(self::$redis->hExists('test_hExists', 'key3'), false);
    }

    //test_hDel
    public function test_hDel()
    {
        $this->assertSame(
            self::$redis->hSet('test_hDel', 'key1', 'value1'),
            1
        );
        $this->assertSame(self::$redis->hDel('test_hDel', 'key1'), 1);
        $this->assertSame(self::$redis->hDel('test_hDel', 'key1'), 0);
        $this->assertSame(
            self::$redis->hSet('test_hDel', 'key1', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->hSet('test_hDel', 'key2', 'value2'),
            1
        );
        $this->assertSame(self::$redis->hDel('test_hDel', 'key1', 'key2'), 2);
        $this->assertSame(self::$redis->hDel('test_hDel', 'key1', 'key2'), 0);
    }

    //test_hLen
    public function test_hLen()
    {
        $this->assertSame(
            self::$redis->hSet('test_hLen', 'key1', 'value1'),
            1
        );
        $this->assertSame(self::$redis->hLen('test_hLen'), 1);
        $this->assertSame(
            self::$redis->hSet('test_hLen', 'key2', 'value2'),
            1
        );
        $this->assertSame(self::$redis->hLen('test_hLen'), 2);
        $this->assertSame(
            self::$redis->hSet('test_hLen', 'key1', 'value3'),
            0
        );
        $this->assertSame(self::$redis->hLen('test_hLen'), 2);
    }

    //test_hStrLen
    public function test_hStrLen()
    {
        $this->assertSame(
            self::$redis->hSet('test_hStrLen', 'key1', 'value1'),
            1
        );
        $this->assertSame(self::$redis->hStrLen('test_hStrLen', 'key1'), 6);
        $this->assertSame(
            self::$redis->hSet('test_hStrLen', 'key2', 'value22'),
            1
        );
        $this->assertSame(self::$redis->hStrLen('test_hStrLen', 'key2'), 7);
        $this->assertSame(
            self::$redis->hSet('test_hStrLen', 'key1', 'value3'),
            0
        );
        $this->assertSame(self::$redis->hStrLen('test_hStrLen', 'key1'), 6);
    }

    //test_hIncrBy
    public function test_hIncrBy()
    {
        $this->assertSame(
            self::$redis->hSet('test_hIncrBy', 'key1', 11),
            1
        );
        $this->assertSame(self::$redis->hIncrBy('test_hIncrBy', 'key1', 1), 12);
        $this->assertSame(self::$redis->hIncrBy('test_hIncrBy', 'key1', -1), 11);
        $this->assertSame(self::$redis->hIncrBy('test_hIncrBy', 'key1', -1), 10);
        $this->assertSame(
            self::$redis->hSet('test_hIncrBy', 'key2', '1'),
            1
        );
        $this->assertSame(self::$redis->hIncrBy('test_hIncrBy', 'key2', 1), 2);
        $this->assertSame(self::$redis->hIncrBy('test_hIncrBy', 'key2', -1), 1);
        $this->assertSame(self::$redis->hIncrBy('test_hIncrBy', 'key2', -1), 0);
    }

    //test_hIncrByFloat
    public function test_hIncrByFloat()
    {
        $this->assertSame(
            self::$redis->hSet('test_hIncrByFloat', 'key1', 11.1),
            1
        );
        $this->assertSame(
            self::$redis->hIncrByFloat('test_hIncrByFloat', 'key1', 1.1),
            12.2
        );
        $this->assertSame(
            self::$redis->hIncrByFloat('test_hIncrByFloat', 'key1', -1.1),
            11.1
        );
        $this->assertSame(
            self::$redis->hIncrByFloat('test_hIncrByFloat', 'key1', -1.1),
            10.0
        );
        $this->assertSame(
            self::$redis->hSet('test_hIncrByFloat', 'key2', 1.11),
            1
        );
        $this->assertSame(
            self::$redis->hIncrByFloat('test_hIncrByFloat', 'key2', 1.1),
            2.21
        );
        $this->assertSame(
            self::$redis->hIncrByFloat('test_hIncrByFloat', 'key2', -1.1),
            1.11
        );
    }

    //test_hMSet
    public function test_hMSet()
    {
        $this->assertSame(
            self::$redis->hMSet('test_hMSet', ['key1' => 'value1', 'key2' => 'value2']),
            true
        );
        $this->assertSame(
            self::$redis->hGet('test_hMSet', 'key1'),
            'value1'
        );
        $this->assertSame(
            self::$redis->hGet('test_hMSet', 'key2'),
            'value2'
        );
        $this->assertSame(
            self::$redis->hMSet('test_hMSet', ['key1' => 'value3', 'key2' => 'value4']),
            true
        );
        $this->assertSame(
            self::$redis->hGet('test_hMSet', 'key1'),
            'value3'
        );
        $this->assertSame(
            self::$redis->hGet('test_hMSet', 'key2'),
            'value4'
        );
        $this->assertSame(
            self::$redis->hMSet('test_hMSet', ['key1' => 'value5', 'key2' => 'value6']),
            true
        );
        $this->assertSame(
            self::$redis->hGet('test_hMSet', 'key1'),
            'value5'
        );
        $this->assertSame(
            self::$redis->hGet('test_hMSet', 'key2'),
            'value6'
        );
    }

    //test_hMGet
    public function test_hMGet()
    {
        $this->assertSame(
            self::$redis->hMSet('test_hMGet', ['key1' => 'value1', 'key2' => 'value2']),
            true
        );
        $this->assertSame(
            self::$redis->hMGet('test_hMGet', ['key1', 'key2']),
            ['key1' => 'value1', 'key2' => 'value2']
        );
        $this->assertSame(
            self::$redis->hMGet('test_hMGet', ['key1', 'key2', 'key3']),
            ['key1' => 'value1', 'key2' => 'value2', 'key3' => false]
        );
    }

    //test_hKeys
    public function test_hKeys()
    {
        $this->assertSame(
            self::$redis->hMSet('test_hKeys', ['key1' => 'value1', 'key2' => 'value2']),
            true
        );
        $this->assertSame(
            self::$redis->hKeys('test_hKeys'),
            ['key1', 'key2']
        );
    }

    //test_hVals
    public function test_hVals()
    {
        $this->assertSame(
            self::$redis->hMSet('test_hVals', ['key1' => 'value1', 'key2' => 'value2']),
            true
        );
        $this->assertSame(
            self::$redis->hVals('test_hVals'),
            ['value1', 'value2']
        );
    }

    //test_hGetAll
    public function test_hGetAll()
    {
        $this->assertSame(
            self::$redis->hMSet('test_hGetAll', ['key1' => 'value1', 'key2' => 'value2']),
            true
        );
        $this->assertSame(
            self::$redis->hGetAll('test_hGetAll'),
            ['key1' => 'value1', 'key2' => 'value2']
        );
    }

    //test_lPush
    public function test_lPush()
    {
        $this->assertSame(
            self::$redis->lPush('test_lPush', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lPush', 'value2', 'value3'),
            3
        );
        $this->assertSame(
            self::$redis->lindex('test_lPush', 0),
            'value3'
        );
        $this->assertSame(
            self::$redis->lindex('test_lPush', 1),
            'value2'
        );
        $this->assertSame(
            self::$redis->lindex('test_lPush', 2),
            'value1'
        );
    }

    //test_lPushx
    public function test_lPushx()
    {
        $this->assertSame(
            self::$redis->lPushx('test_lPushx', 'value1'),
            0
        );
        $this->assertSame(
            self::$redis->lPush('test_lPushx', 'x'),
            1
        );
        $this->assertSame(
            self::$redis->lPushx('test_lPushx', 'value1'),
            2
        );
        $this->assertSame(
            self::$redis->lPushx('test_lPushx', 'value2'),
            3
        );
        $this->assertSame(
            self::$redis->lindex('test_lPushx', 0),
            'value2'
        );
        $this->assertSame(
            self::$redis->lindex('test_lPushx', 1),
            'value1'
        );
        $this->assertSame(
            self::$redis->lindex('test_lPushx', 2),
            'x'
        );
    }

    //test_rPush
    public function test_rPush()
    {
        $this->assertSame(
            self::$redis->rPush('test_rPush', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->rPush('test_rPush', 'value2', 'value3'),
            3
        );
        $this->assertSame(
            self::$redis->lindex('test_rPush', 0),
            'value1'
        );
        $this->assertSame(
            self::$redis->lindex('test_rPush', 1),
            'value2'
        );
        $this->assertSame(
            self::$redis->lindex('test_rPush', 2),
            'value3'
        );
    }

    //test_rPushx
    public function test_rPushx()
    {
        $this->assertSame(
            self::$redis->rPushx('test_rPushx', 'value1'),
            0
        );
        $this->assertSame(
            self::$redis->rPush('test_rPushx', 'x'),
            1
        );
        $this->assertSame(
            self::$redis->rPushx('test_rPushx', 'value1'),
            2
        );
        $this->assertSame(
            self::$redis->rPushx('test_rPushx', 'value2'),
            3
        );
        $this->assertSame(
            self::$redis->lindex('test_rPushx', 0),
            'x'
        );
        $this->assertSame(
            self::$redis->lindex('test_rPushx', 1),
            'value1'
        );
        $this->assertSame(
            self::$redis->lindex('test_rPushx', 2),
            'value2'
        );
    }

    //test_lPop
    public function test_lPop()
    {
        $this->assertSame(
            self::$redis->lPush('test_lPop', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lPop', 'value2', 'value3'),
            3
        );
        $this->assertSame(
            self::$redis->lPop('test_lPop'),
            'value3'
        );
        $this->assertSame(
            self::$redis->lPop('test_lPop'),
            'value2'
        );
        $this->assertSame(
            self::$redis->lPop('test_lPop'),
            'value1'
        );
        $this->assertSame(
            self::$redis->lPop('test_lPop'),
            false
        );
    }

    //test_rPop
    public function test_rPop()
    {
        $this->assertSame(
            self::$redis->rPush('test_rPop', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->rPush('test_rPop', 'value2', 'value3'),
            3
        );
        $this->assertSame(
            self::$redis->rPop('test_rPop'),
            'value3'
        );
        $this->assertSame(
            self::$redis->rPop('test_rPop'),
            'value2'
        );
        $this->assertSame(
            self::$redis->rPop('test_rPop'),
            'value1'
        );
        $this->assertSame(
            self::$redis->rPop('test_rPop'),
            false
        );
    }

    //test_rPoplPush
    public function test_rPoplPush()
    {
        $this->assertSame(
            self::$redis->rPush('test_rPoplPush', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->rPush('test_rPoplPush', 'value2', 'value3'),
            3
        );
        $this->assertSame(
            self::$redis->rPoplPush('test_rPoplPush', 'test_rPoplPush2'),
            'value3'
        );
        $this->assertSame(
            self::$redis->rPoplPush('test_rPoplPush', 'test_rPoplPush2'),
            'value2'
        );
        $this->assertSame(
            self::$redis->rPoplPush('test_rPoplPush', 'test_rPoplPush2'),
            'value1'
        );
        $this->assertSame(
            self::$redis->rPoplPush('test_rPoplPush', 'test_rPoplPush2'),
            false
        );
    }

    //test_lRem
    public function test_lRem()
    {
        $this->assertSame(
            self::$redis->lPush('test_lRem', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lRem', 'value2', 'value1', 'value2', 'value1'),
            5
        );
        $this->assertSame(
            self::$redis->lRem('test_lRem', 'value1', 1),
            1
        );
        $this->assertSame(
            self::$redis->lRem('test_lRem', 'value1', 0),
            2
        );
        $this->assertSame(
            self::$redis->lRem('test_lRem', 'value1', -1),
            0
        );
        $this->assertSame(
            self::$redis->lRem('test_lRem', 'value2', 0),
            2
        );
    }

    //test_lLen
    public function test_lLen()
    {
        $this->assertSame(
            self::$redis->lPush('test_lLen', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lLen', 'value2', 'value1', 'value2', 'value1'),
            5
        );
        $this->assertSame(
            self::$redis->lLen('test_lLen'),
            5
        );
    }

    //test_lIndex
    public function test_lIndex()
    {
        $this->assertSame(
            self::$redis->lPush('test_lIndex', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lIndex', 'value2', 'value1', 'value2', 'value1'),
            5
        );
        $this->assertSame(
            self::$redis->lIndex('test_lIndex', 0),
            'value1'
        );
        $this->assertSame(
            self::$redis->lIndex('test_lIndex', 1),
            'value2'
        );
        $this->assertSame(
            self::$redis->lIndex('test_lIndex', 2),
            'value1'
        );
        $this->assertSame(
            self::$redis->lIndex('test_lIndex', 3),
            'value2'
        );
        $this->assertSame(
            self::$redis->lIndex('test_lIndex', 4),
            'value1'
        );
        $this->assertSame(
            self::$redis->lIndex('test_lIndex', 5),
            false
        );
    }

    //test_lInsert
    public function test_lInsert()
    {
        $this->assertSame(
            self::$redis->lPush('test_lInsert', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lInsert', 'value2', 'value1', 'value2'),
            4
        );
        $this->assertSame(
            self::$redis->lInsert('test_lInsert', \Redis::AFTER, 'value2', 'value3'),
            5
        );
        $this->assertSame(
            self::$redis->lInsert('test_lInsert', \Redis::AFTER, 'value10', 'value3'),
            -1
        );
        $this->assertSame(
            self::$redis->lInsert('test_lInsert2', \Redis::AFTER, 'value10', 'value3'),
            0
        );
        $this->assertSame(
            self::$redis->lInsert('test_hVals', \Redis::AFTER, 'value10', 'value3'),
            false
        );
        $this->assertSame(
            self::$redis->lpop('test_lInsert'),
            'value2'
        );
        $this->assertSame(
            self::$redis->lpop('test_lInsert'),
            'value3'
        );
    }

    //test_lSet
    public function test_lSet()
    {
        $this->assertSame(
            self::$redis->lSet('test_lSet', 0, 'value3'),
            false
        );
        $this->assertSame(
            self::$redis->lPush('test_lSet', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lSet', 'value2', 'value1', 'value2'),
            4
        );
        $this->assertSame(
            self::$redis->lSet('test_lSet', 0, 'value3'),
            true
        );
        $this->assertSame(
            self::$redis->lSet('test_lSet', 1, 'value4'),
            true
        );
        $this->assertSame(
            self::$redis->lSet('test_lSet', 2, 'value5'),
            true
        );
        $this->assertSame(
            self::$redis->lSet('test_lSet', 3, 'value6'),
            true
        );
        $this->assertSame(
            self::$redis->lSet('test_lSet', 4, 'value7'),
            false
        );
        $this->assertSame(
            self::$redis->lpop('test_lSet'),
            'value3'
        );
        $this->assertSame(
            self::$redis->lpop('test_lSet'),
            'value4'
        );
        $this->assertSame(
            self::$redis->lpop('test_lSet'),
            'value5'
        );
        $this->assertSame(
            self::$redis->lpop('test_lSet'),
            'value6'
        );
    }

    //test_lRange
    public function test_lRange()
    {
        $this->assertSame(
            self::$redis->lPush('test_lRange', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lRange', 'value4', 'value3', 'value2', 'value1'),
            5
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, 0),
            ['value1']
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, 1),
            ['value1', 'value2']
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, 2),
            ['value1', 'value2', 'value3']
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, 3),
            ['value1', 'value2', 'value3', 'value4']
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, 4),
            ['value1', 'value2', 'value3', 'value4', 'value1']
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, 5),
            ['value1', 'value2', 'value3', 'value4', 'value1']
        );
        $this->assertSame(
            self::$redis->lRange('test_lRange', 0, -1),
            ['value1', 'value2', 'value3', 'value4', 'value1']
        );
    }

    //test_lTrim
    public function test_lTrim()
    {
        $this->assertSame(
            self::$redis->lPush('test_lTrim', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->lPush('test_lTrim', 'value2', 'value3', 'value4', 'value5'),
            5
        );
        $this->assertSame(
            self::$redis->lTrim('test_lTrim', 1, 2),
            true
        );
        $this->assertSame(
            self::$redis->lRange('test_lTrim', 0, 5),
            ['value4', 'value3']
        );
    }

    //zAdd
    public function test_zAdd()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zAdd', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zAdd', 2, 'value2'),
            1
        );
        //ZRANGE
        $this->assertSame(
            self::$redis->zRange('test_zAdd', 0, -1, true),
            ['value1' => 1.0, 'value2' => 2.0]
        );
    }

    //zScore
    public function test_zScore()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zScore', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zScore', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zScore('test_zScore', 'value1'),
            1.0
        );
        $this->assertSame(
            self::$redis->zScore('test_zScore', 'value2'),
            2.0
        );
    }

    //zRank
    public function test_zRank()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRank', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRank', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRank('test_zRank', 'value1'),
            0
        );
        $this->assertSame(
            self::$redis->zRank('test_zRank', 'value2'),
            1
        );
    }

    //zRevRank
    public function test_zRevRank()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRevRank', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRevRank', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRevRank('test_zRevRank', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zRevRank('test_zRevRank', 'value2'),
            0
        );
    }

    //zIncrBy
    public function test_zIncrBy()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zIncrBy', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zIncrBy', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zIncrBy('test_zIncrBy', 1, 'value1'),
            2.0
        );
        $this->assertSame(
            self::$redis->zIncrBy('test_zIncrBy', 2, 'value2'),
            4.0
        );
    }

    //zCard
    public function test_zCard()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zCard', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zCard', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zCard('test_zCard'),
            2
        );
    }

    //zCount
    public function test_zCount()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zCount', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zCount', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zCount('test_zCount', 0, 1),
            1
        );
        $this->assertSame(
            self::$redis->zCount('test_zCount', 0, 2),
            2
        );
        $this->assertSame(
            self::$redis->zCount('test_zCount', 0, 3),
            2
        );
        $this->assertSame(
            self::$redis->zCount('test_zCount', 0, 4),
            2
        );
        $this->assertSame(
            self::$redis->zCount('test_zCount', 0, 5),
            2
        );
    }

    //zPopMin
    public function test_zPopMin()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zPopMin', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zPopMin', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zPopMin('test_zPopMin', 1),
            ['value1' => 1.0]
        );
        $this->assertSame(
            self::$redis->zPopMin('test_zPopMin', 1),
            ['value2' => 2.0]
        );
    }

    //zPopMax
    public function test_zPopMax()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zPopMax', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zPopMax', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zPopMax('test_zPopMax', 1),
            ['value2' => 2.0]
        );
        $this->assertSame(
            self::$redis->zPopMax('test_zPopMax', 1),
            ['value1' => 1.0]
        );
    }

    //zRange
    public function test_zRange()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRange', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRange', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRange('test_zRange', 0, -1),
            ['value1', 'value2']
        );
        $this->assertSame(
            self::$redis->zRange('test_zRange', 0, -1, true),
            ['value1' => 1.0, 'value2' => 2.0]
        );
    }

    //zRevRange
    public function test_zRevRange()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRevRange', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRevRange', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRevRange('test_zRevRange', 0, -1),
            ['value2', 'value1']
        );
        $this->assertSame(
            self::$redis->zRevRange('test_zRevRange', 0, -1, true),
            ['value2' => 2.0, 'value1' => 1.0]
        );
    }

    //zRangeByScore
    public function test_zRangeByScore()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRangeByScore', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRangeByScore', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRangeByScore('test_zRangeByScore', 0, 1),
            ['value1']
        );
        $this->assertSame(
            self::$redis->zRangeByScore('test_zRangeByScore', 0, 1, ['withscores' => true, 'limit' => [0, 1]]),
            ['value1' => 1.0]
        );
        $this->assertSame(
            self::$redis->zRangeByScore('test_zRangeByScore', 0, 2),
            ['value1', 'value2']
        );
        $this->assertSame(
            self::$redis->zRangeByScore('test_zRangeByScore', 0, 2, ['withscores' => true, 'limit' => [0, 2]]),
            ['value1' => 1.0, 'value2' => 2.0]
        );
    }

    //zRevRangeByScore
    // public function test_zRevRangeByScore()
    // {
    //     self::$redis->zAdd('test_zRevRangeByScore', 0, 'val0');
    //     self::$redis->zAdd('test_zRevRangeByScore', 2, 'val2');
    //     self::$redis->zAdd('test_zRevRangeByScore', 10, 'val10');
    //     var_dump(self::$redis->zRevRangeByScore('test_zRevRangeByScore', '-inf', '+inf'));exit;
    //     $this->assertSame(
    //         self::$redis->zRangeByScore('test_zRevRangeByScore', 0, 3),
    //         ['val0', 'val2']
    //     );
    // }

    //zRem
    public function test_zRem()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRem', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRem', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRem('test_zRem', 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zRem('test_zRem', 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRem('test_zRem', 'value1'),
            0
        );
        $this->assertSame(
            self::$redis->zRem('test_zRem', 'value2'),
            0
        );
    }

    //zRemRangeByRank
    public function test_zRemRangeByRank()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRemRangeByRank', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRemRangeByRank', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRemRangeByRank('test_zRemRangeByRank', 0, 0),
            1
        );
        $this->assertSame(
            self::$redis->zRemRangeByRank('test_zRemRangeByRank', 0, 0),
            1
        );
        $this->assertSame(
            self::$redis->zRemRangeByRank('test_zRemRangeByRank', 0, 1),
            0
        );
        $this->assertSame(
            self::$redis->zRange('test_zRemRangeByRank', 0, -1),
            []
        );
    }

    //zRemRangeByScore
    public function test_zRemRangeByScore()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRemRangeByScore', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRemRangeByScore', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRemRangeByScore('test_zRemRangeByScore', 0, 1),
            1
        );
        $this->assertSame(
            self::$redis->zRemRangeByScore('test_zRemRangeByScore', 0, 1),
            0
        );
        $this->assertSame(
            self::$redis->zRemRangeByScore('test_zRemRangeByScore', 0, 2),
            1
        );
        $this->assertSame(
            self::$redis->zRange('test_zRemRangeByScore', 0, -1),
            []
        );
    }

    //zRangeByLex
    public function test_zRangeByLex()
    {
        $i = 1;
        foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g'] as $c) {
            self::$redis->zAdd('test_zRangeByLex', $i++, $c);
        }
        $this->assertSame(
            self::$redis->zRangeByLex('test_zRangeByLex', '-', '[c'),
            ['a', 'b', 'c']
        );
        $this->assertSame(
            self::$redis->zRangeByLex('test_zRangeByLex', '-', '+'),
            ['a', 'b', 'c', 'd', 'e', 'f', 'g']
        );
        $this->assertSame(
            self::$redis->zRangeByLex('test_zRangeByLex', '[a', '[c'),
            ['a', 'b', 'c']
        );
    }

    //zLexCount
    public function test_zLexCount()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zLexCount', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zLexCount', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zLexCount('test_zLexCount', '[value1', '[value2'),
            2
        );
        $this->assertSame(
            self::$redis->zLexCount('test_zLexCount', '(value1', '(value2'),
            0
        );
    }

    //zRemRangeByLex
    public function test_zRemRangeByLex()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zRemRangeByLex', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zRemRangeByLex', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zRemRangeByLex('test_zRemRangeByLex', '[value1', '[value2'),
            2
        );
        $this->assertSame(
            self::$redis->zRange('test_zRemRangeByLex', 0, -1),
            []
        );
    }

    //zUnionStore
    public function test_zUnionStore()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zUnionStore', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zUnionStore', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zUnionStore', 3, 'value3'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zUnionStore2', 3, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zUnionStore2', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zUnionStore2', 1, 'value3'),
            1
        );
        $this->assertSame(
            self::$redis->zUnionStore('test_zUnionStore_new', ['test_zUnionStore2', 'test_zUnionStore'], [1, 2]),
            3
        );
        $this->assertSame(
            self::$redis->zRange('test_zUnionStore_new', 0, -1),
            ['value1', 'value2', 'value3']
        );
    }

    //zInterStore
    public function test_zInterStore()
    {
        $this->assertSame(
            self::$redis->zAdd('test_zInterStore', 1, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zInterStore', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zInterStore', 3, 'value3'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zInterStore2', 3, 'value1'),
            1
        );
        $this->assertSame(
            self::$redis->zAdd('test_zInterStore2', 2, 'value2'),
            1
        );
        $this->assertSame(
            self::$redis->zInterStore('test_zInterStore_new', ['test_zInterStore2', 'test_zInterStore'], [1, 2]),
            2
        );
        $this->assertSame(
            self::$redis->zRange('test_zInterStore_new', 0, -1, true),
            ['value1' => 5.0, 'value2' => 6.0]
        );
    }
}
