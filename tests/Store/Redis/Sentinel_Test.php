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
use Tabby\Store\Redis\Sentinel;
use Tabby\Tabby;
use Tabby\Test\TestCase;
use Tabby\Utils\StrUtils;
use Tabby\Utils\Timer;

class Sentinel_Test extends TestCase
{
    public static $conf;
    public static $cacheFile;
    public static $masterSlavesConf;

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
            Conf::helper()
        );
        self::$cacheFile = StrUtils::dirLastSeparator(Tabby::$Conf['tabby']['tmpPath']) . 'redis.conf.php';
    }

    public function test_basic()
    {
        // 删主从配置缓存
        if (file_exists(self::$cacheFile)) {
            unlink(self::$cacheFile);
        }
        $this->assertFalse(file_exists(self::$cacheFile));

        // 实例化哨兵, 重新从哨兵拉取
        $sent = new Sentinel(self::$conf);
        // 验证是否拉取成功
        $this->assertTrue(file_exists(self::$cacheFile));
        // 缓存数据是否正常
        $confCache = include self::$cacheFile;
        $this->assertTrue(is_array($confCache['master']));
        $this->assertTrue(
            is_array($confCache['slaves']) &&
            count($confCache['slaves']) > 0 &&
            isset($confCache['slaves'][0]['host'])
        );

        // set get 测 redis 访问是否正常
        $this->assertTrue($sent->masterExec('set', ['a', 'test']));
        $this->assertSame($sent->slaveExec('get', ['a']), 'test');

        // 缓存文件最后修改时间
        $mtime = filectime(self::$cacheFile);
        sleep(1);
        // 刷新配置缓存
        self::$masterSlavesConf = $sent->flushConf();
        // 检查缓存是否重写过
        clearstatcache(true, self::$cacheFile);
        $this->assertTrue($mtime !== filectime(self::$cacheFile));

        // 检查缓存内容
        $this->assertTrue(self::$masterSlavesConf instanceof Conf);
        $this->assertTrue(isset(self::$masterSlavesConf->getMaster()['host']));
        $this->assertTrue(isset(self::$masterSlavesConf->getSlaves()[0]['host']));

        // 有缓存应该使用缓存, 不会调用flushConf方法
        $this->assertTrue(file_exists(self::$cacheFile));

        /**
         * @var \Object
         */
        $mockSentinel = $this->createMock(Sentinel::class);
        $mockSentinel->expects($this->never())->method('flushConf');
        $mockSentinel->__construct(self::$conf);
    }

    public function test_advanced()
    {
        // 模拟切主, 主变从场景 自动刷新主从配置
        $conf                   = include self::$cacheFile;
        $conf['master']['port'] = $conf['slaves'][0]['port'];
        $this->assertTrue(
            file_put_contents(self::$cacheFile, "<?php\nreturn " . var_export($conf, true) . ';') !== false
        );
        $sent = new Sentinel(self::$conf);
        $this->assertTrue($sent->masterExec('set', ['a', 'readonly_test']));
        $this->assertSame($sent->slaveExec('get', ['a']), 'readonly_test');

        // 模拟主挂掉
        $conf                   = include self::$cacheFile;
        $conf['master']['port'] = 33300;
        $this->assertTrue(
            file_put_contents(self::$cacheFile, "<?php\nreturn " . var_export($conf, true) . ';') !== false
        );
        $sent = new Sentinel(self::$conf);
        $this->assertTrue($sent->masterExec('set', ['a', 'master_down_test']));
        $this->assertSame($sent->slaveExec('get', ['a']), 'master_down_test');

        // 模拟从挂掉
        $conf                      = include self::$cacheFile;
        $conf['slaves'][0]['port'] = 33300;
        $conf['slaves'][1]['port'] = 33300;
        $this->assertTrue(
            file_put_contents(self::$cacheFile, "<?php\nreturn " . var_export($conf, true) . ';') !== false
        );
        $sent = new Sentinel(self::$conf);
        $this->assertTrue($sent->masterExec('set', ['a', 'slave_down_test']));
        $this->assertSame($sent->slaveExec('get', ['a']), 'slave_down_test');

        // 模拟哨兵挂掉(剩一个)
        $conf = Conf::sentinel(
            'mymaster',
            [
                Conf::helper('127.0.0.1', 33300),
                Conf::helper('127.0.0.1', 26380),
                Conf::helper('127.0.0.1', 33300),
            ],
            Conf::helper()
        );
        for ($i = 0; $i < 30; $i++) {
            Tabby::$Log->debug('===sentinel_down_test_' . $i);
            if (file_exists(self::$cacheFile)) {
                unlink(self::$cacheFile);
            }

            clearstatcache(true, self::$cacheFile);
            $this->assertFalse(file_exists(self::$cacheFile));

            $sent = new Sentinel($conf);
            $this->assertTrue($sent->masterExec('set', ['a', 'sentinel_down_test_' . $i]));
            $this->assertSame($sent->slaveExec('get', ['a']), 'sentinel_down_test_' . $i);
        }
    }
}
