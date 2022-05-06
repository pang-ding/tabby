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

use Tabby\Store\Mysql\Delete;
use Tabby\Test\TestCase;

class Delete_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_getType()
    {
        $this->assertSame(Delete::SQL_TYPE, 'delete');
        $this->assertSame(Delete::SQL_TYPE, (new Delete('table'))->getSqlType());
    }

    public function test_build()
    {
        $delete = new Delete('table');
        $delete->where(['id|>' => 100]);
        $delete->orderBy('id');
        $delete->limit(1);
        $sql = $this->call($delete, 'build');
        $this->assertSame($sql, 'DELETE FROM table WHERE id > :b_v_1 ORDER BY id LIMIT 1');
        $this->assertSame($sql, $delete->getSql());
    }
}
