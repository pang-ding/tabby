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

use Tabby\Store\Mysql\Update;
use Tabby\Test\TestCase;

class Update_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_getType()
    {
        $this->assertSame(Update::SQL_TYPE, 'update');
        $this->assertSame(Update::SQL_TYPE, (new Update('table'))->getSqlType());
    }

    public function test_build()
    {
        $update = new Update('table');
        $update->set(['a' => 1, 'b' => 2]);
        $update->where(['id' => 1]);
        $update->orderBy('id');
        $update->limit(1);
        $sql = $this->call($update, 'build');
        $this->assertSame($sql, 'UPDATE table SET a=:b_v_1, b=:b_v_2 WHERE id = :b_v_3 ORDER BY id LIMIT 1');
        $this->assertSame($sql, $update->getSql());
    }
}
