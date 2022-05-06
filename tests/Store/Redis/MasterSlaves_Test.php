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
namespace Tabby\Test\Store\Redis;

use Tabby\Store\Redis\Conf;
use Tabby\Store\Redis\MasterSlaves;
use Tabby\Test\TestCase;

class MasterSlaves_Test extends TestCase
{
    public static $conf;
    public static $confArray;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        self::$confArray = include HOME_PATH . '/tmp/redis.conf.php';
        self::$conf = Conf::masterSlave(
            self::$confArray['master'],
            self::$confArray['slaves']
        );
    }

    public function test_basic()
    {
        $redis = new MasterSlaves(self::$conf);
        // set get 测 redis 访问是否正常
        $this->assertTrue(
            $redis->masterExec('set', ['a', 'testMasterSlaves'])
        );
        $this->assertSame($redis->slaveExec('get', ['a']), 'testMasterSlaves');
    }

    public function test_advanced()
    {
        // slaves = []
        $conf = Conf::masterSlave(
            Conf::helper('127.0.0.1', self::$confArray['master']['port']),
            []
        );
        $redis = new MasterSlaves(self::$conf);
        $this->assertTrue(
            $redis->masterExec('set', ['a', 'testMasterSlaves_empty_slaves'])
        );
        $this->assertSame($redis->slaveExec('get', ['a']), 'testMasterSlaves_empty_slaves');

        // onlyMaster
        $conf = Conf::masterSlave(
            Conf::helper('127.0.0.1', self::$confArray['master']['port']),
            [
                Conf::helper('127.0.0.1', 9999),
                Conf::helper('127.0.0.1', 9999),
            ],
            true
        );
        $redis = new MasterSlaves($conf);
        $this->assertTrue(
            $redis->masterExec('set', ['a', 'testMasterSlaves_only_master'])
        );
        $this->assertSame($redis->slaveExec('get', ['a']), 'testMasterSlaves_only_master');

        // 从配错 验证onlyMaster
        $conf = Conf::masterSlave(
            Conf::helper('127.0.0.1', self::$confArray['master']['port']),
            [
                Conf::helper('127.0.0.1', 9999),
                Conf::helper('127.0.0.1', 9999),
            ],
            false
        );
        $redis = new MasterSlaves($conf);
        $this->assertTrue(
            $redis->masterExec('set', ['a', 'testMasterSlaves_slaves_error'])
        );
        $this->assertException(
            \RedisException::class,
            function () use ($redis) {
                $redis->slaveExec('get', ['a']);
            }
        );
    }
}
