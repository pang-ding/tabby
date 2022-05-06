<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Store\Mysql;

use Tabby\Store\Mysql\Conn;
use Tabby\Test\Context;
use Tabby\Test\Store\Mysql\Config;
use Tabby\Test\TestCase;

class Conn_Test extends TestCase
{
    /**
     * Host
     *
     * @var Conn
     */
    protected static $_conn;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        self::$_conn = new Conn(
            Context::$mysqlConf['dsn'],
            Context::$mysqlConf['username'],
            Context::$mysqlConf['password'],
            [
                \PDO::ATTR_AUTOCOMMIT => 1,
                \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => false,
            ]
        );
    }

    public function test_init()
    {
        self::$_conn = new Conn(
            Context::$mysqlConf['dsn'],
            Context::$mysqlConf['username'],
            Context::$mysqlConf['password'],
            [
                \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => true,
            ],
            ['set session wait_timeout=1;']
        );

        $this->assertSame(true, self::$_conn->getAttribute(\PDO::ATTR_PERSISTENT));
        $this->assertSame(\PDO::ERRMODE_EXCEPTION, self::$_conn->getAttribute(\PDO::ATTR_ERRMODE));
        $rst = self::$_conn->fetchRow("show variables like 'wait_timeout'");
        $this->assertSame('1', $rst['Value']);

        // 建表
        self::$_conn->exec(Tables::$table_user);
        self::$_conn->exec(Tables::$table_group);
        self::$_conn->exec(Tables::$table_class);
    }

    public function test_ping()
    {
        $this->assertSame(true, is_string(self::$_conn->getAttribute(\PDO::ATTR_SERVER_INFO)));
        usleep(1010000);
        $this->assertException(\PDOException::class, function () {
            @self::$_conn->getAttribute(\PDO::ATTR_SERVER_INFO);
        });
        self::$_conn->ping();
        $this->assertSame(true, is_string(self::$_conn->getAttribute(\PDO::ATTR_SERVER_INFO)));
        self::$_conn->exec('set session wait_timeout=100;');
    }

    public function test_setAttribute()
    {
        self::$_conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $this->assertSame(\PDO::ERRMODE_SILENT, self::$_conn->getAttribute(\PDO::ATTR_ERRMODE));
    }

    public function test_begin()
    {
        $this->assertSame(false, self::$_conn->inTransaction());
        self::$_conn->begin();
        $this->assertSame(true, self::$_conn->inTransaction());
        self::$_conn->rollBack();
    }

    public function test_rollBack()
    {
        $count = self::$_conn->fetchValue('SELECT COUNT(1) FROM `group`');
        self::$_conn->begin();
        self::$_conn->exec('INSERT INTO `group` SET `name` = "A"');
        self::$_conn->rollBack();
        $this->assertSame($count, self::$_conn->fetchValue('SELECT COUNT(1) FROM `group`'));
    }

    public function test_commit()
    {
        $count = self::$_conn->fetchValue('SELECT COUNT(1) FROM `group`');
        self::$_conn->begin();
        self::$_conn->exec('INSERT INTO `group` SET `name` = "A"');
        self::$_conn->commit();
        $this->assertSame($count + 1, (int) self::$_conn->fetchValue('SELECT COUNT(1) FROM `group`'));
    }

    public function test_lastInsertId()
    {
        $AUTO_INCREMENT = self::$_conn->fetchValue('SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` where `TABLE_NAME` = "group";');
        self::$_conn->exec('INSERT INTO `group` SET `name` = "B"');
        $this->assertSame($AUTO_INCREMENT, self::$_conn->lastInsertId());
    }

    public function test_execute()
    {
        $rst = self::$_conn->execute('INSERT INTO `group` SET `name` = :name', ['name' => 'C']);
        $this->assertSame(1, $rst);
    }

    public function test_fatchAll()
    {
        $count = self::$_conn->fetchValue('SELECT COUNT(1) FROM `group`');
        $rst   = self::$_conn->fetchAll('SELECT * FROM `group` WHERE `id` > :id', ['id' => 0]);
        $this->assertSame((int) $count, count($rst));
    }

    public function test_yield()
    {
        $count = self::$_conn->fetchValue('SELECT COUNT(1) FROM `group`');
        $rst   = self::$_conn->yield('SELECT * FROM `group`');

        $data = [];
        foreach ($rst as $v) {
            $data[] = $v;
        }
        $this->assertSame((int) $count, count($data));
    }

    public function test_fatchRow()
    {
        $rst = self::$_conn->fetchRow('SELECT * FROM `group` WHERE `id` > :id', ['id' => 0]);
        $this->assertSame(true, isset($rst['id']) && isset($rst['name']));
    }
}
