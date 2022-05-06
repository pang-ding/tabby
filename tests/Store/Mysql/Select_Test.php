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

use Tabby\Store\Mysql\Select;
use Tabby\Test\TestCase;

class Select_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetType()
    {
        $this->assertSame(Select::SQL_TYPE, 'select');
        $this->assertSame(Select::SQL_TYPE, (new Select())->getSqlType());
    }

    public function testSelect()
    {
        $s = new Select();
        $this->assertSame(static::getValue($s, '_select'), 'SELECT *');
        $s->select('count(1) as `ct`');
        $this->assertSame(static::getValue($s, '_select'), 'SELECT count(1) as `ct`');
        $s = new Select(['`name`', '`age`', '`level`']);
        $this->assertSame(static::getValue($s, '_select'), 'SELECT `name`, `age`, `level`');
        $s = new Select(['`user`' => ['`name`', '`age`', '`level`'], '`group`' => ['`group_id`']]);
        $this->assertSame(static::getValue($s, '_select'), 'SELECT `user`.`name`, `user`.`age`, `user`.`level`, `group`.`group_id`');
    }

    public function testFrom()
    {
        $s = new Select();
        $s->from('table');
        $this->assertSame(static::getValue($s, '_from'), ' FROM table');
        $s->from('table', 'tb');
        $this->assertSame(static::getValue($s, '_from'), ' FROM table AS tb');
    }

    public function testJoin()
    {
        $s = new Select();
        $s->join('t1', 'c1', 't2', 'c2');
        $this->assertSame($this->call($s, 'buildJoin'), ' JOIN t1 ON t2.c2 = t1.c1');
        $s = new Select();
        $s->leftJoin('t1', 'c1', 't2', 'c2', 'linkt', 'key1');
        $this->assertSame($this->call($s, 'buildJoin'), ' LEFT JOIN t1 AS linkt ON t2.c2 = linkt.c1');
        $s->joinBySql('FULL JOIN t3 ON t1.c1=t3.c3', 'key2');
        $this->assertSame($this->call($s, 'buildJoin'), ' LEFT JOIN t1 AS linkt ON t2.c2 = linkt.c1 FULL JOIN t3 ON t1.c1=t3.c3');
        $s->unsetJoin('key1');
        $this->assertSame($this->call($s, 'buildJoin'), ' FULL JOIN t3 ON t1.c1=t3.c3');
    }

    public function testWhere()
    {
        $s = new Select();
        $s->where('`user` LIKE "%abc%"', 0, 'key1');
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE `user` LIKE "%abc%"');
        $s->where(['c1|in' => [1, 2, 3]], 0, 'key2');
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE (`user` LIKE "%abc%") AND (c1 in (:b_v_1, :b_v_2, :b_v_3))');
        $s->unsetWhere('key1');
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE c1 in (:b_v_1, :b_v_2, :b_v_3)');
        $s = new Select();
        $s->where(['c1|<=' => '10', 'c2|>' => 20]);
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE c1 <= :b_v_1 AND c2 > :b_v_2');
        $s = new Select();
        $s->where(['c1|LIKE' => '%abc%', 'c2|<>' => 20], true);
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE c1 LIKE :b_v_1 OR c2 <> :b_v_2');
        $s = new Select();
        $s->where([['c1' => 1, 'c2' => 2], ['c3' => 3, 'c4' => 4]]);
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE (c1 = :b_v_1 OR c2 = :b_v_2) AND (c3 = :b_v_3 OR c4 = :b_v_4)');
        $s = new Select();
        $s->where([['c1' => 1, 'c2' => 2], ['c3' => 3, 'c4' => 4], 'c5 in (1,2,3,4,5)'], true);
        $this->assertSame($this->call($s, 'buildWhere'), ' WHERE (c1 = :b_v_1 AND c2 = :b_v_2) OR (c3 = :b_v_3 AND c4 = :b_v_4) OR (c5 in (1,2,3,4,5))');
    }

    public function testGroupBy()
    {
        $s = new Select();
        $s->groupBy('`user`, age');
        $this->assertSame(static::getValue($s, '_groupBy'), ' GROUP BY `user`, age');
        $s->groupBy('');
        $this->assertSame(static::getValue($s, '_groupBy'), '');
    }

    public function testHiving()
    {
        $s = new Select();
        $s->having('count(`user`) > 1');
        $this->assertSame(static::getValue($s, '_having'), ' HAVING count(`user`) > 1');
        $s->groupBy('');
        $this->assertSame(static::getValue($s, '_groupBy'), '');
    }

    public function testOrderBy()
    {
        $s = new Select();
        $s->orderBy('`user` DESC, age');
        $this->assertSame(static::getValue($s, '_orderBy'), ' ORDER BY `user` DESC, age');
        $s->orderBy('');
        $this->assertSame(static::getValue($s, '_orderBy'), '');
    }

    public function testLimit()
    {
        $s = new Select();
        $s->limit(10);
        $this->assertSame(static::getValue($s, '_limit'), ' LIMIT 10');
        $s->limit(10, 100);
        $this->assertSame(static::getValue($s, '_limit'), ' LIMIT 100, 10');
        $s->limit(0);
        $this->assertSame(static::getValue($s, '_limit'), '');
    }

    public function testForUpdate()
    {
        $s = new Select();
        $s->forUpdate();
        $this->assertSame(static::getValue($s, '_forUpdate'), ' FOR UPDATE');
        $s->forUpdate(false);
        $this->assertSame(static::getValue($s, '_forUpdate'), '');
    }

    public function testBuild()
    {
        $s = (new Select('*'))
            ->from('a')
            ->join('t2', 'c2', 't1', 'c1')
            ->leftJoin('t3', 'c3', 't1', 'c1', 'as_t3')
            ->where(['c1' => 1])
            ->groupBy('c1')
            ->having(['count(c1_1)' => 10])
            ->orderBy('c1')
            ->limit(10)
            ->forUpdate();
        $sql = $this->call($s, 'build');
        $this->assertSame(
            'SELECT * FROM a JOIN t2 ON t1.c1 = t2.c2 LEFT JOIN t3 AS as_t3 ON t1.c1 = as_t3.c3 WHERE c1 = :b_v_1 GROUP BY c1 HAVING count(c1_1) = :b_v_2 ORDER BY c1 LIMIT 10 FOR UPDATE',
            $s->getSql()
        );
        $this->assertSame($sql, $s->getSql());
    }
}
