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

use Tabby\Store\Mysql\Insert;
use Tabby\Test\TestCase;

class Insert_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_getType()
    {
        $this->assertSame(Insert::SQL_TYPE, 'insert');
        $this->assertSame(Insert::SQL_TYPE, (new Insert('table'))->getSqlType());
    }

    public function test_set()
    {
        $insert = new Insert('table');
        $insert->set(['a' => 1, 'b' => 2]);
        $this->assertSame(static::getValue($insert, '_set'), ' SET a=:b_v_1, b=:b_v_2');
    }

    public function test_build()
    {
        $insert = new Insert('table');
        $insert->set(['a' => 1, 'b' => 2]);
        $sql = $this->call($insert, 'build');
        $this->assertSame($sql, 'INSERT INTO table SET a=:b_v_1, b=:b_v_2');
        $this->assertSame($sql, $insert->getSql());
    }
}
