<?php
namespace Tabby\Test\Store\Mysql;

use Tabby\Store\Mysql\Conn;
use Tabby\Store\Mysql\DB;
use Tabby\Test\Context;
use Tabby\Test\TestCase;

class DB_Test extends TestCase
{
    /**
     * Host
     *
     * @var DB
     */
    protected static $_db;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        self::$_db = new DB(new Conn(
            Context::$mysqlConf['dsn'],
            Context::$mysqlConf['username'],
            Context::$mysqlConf['password'],
            [
                \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => true,
            ]
        ));
    }

    public function test_sql_insert()
    {
        self::$_db->sql(Tables::$table_user)->exec();
        self::$_db->sql(Tables::$table_group)->exec();
        self::$_db->sql(Tables::$table_class)->exec();

        $date = date('Y-m-d H:i:s');

        $this->assertSame('1', self::$_db->insert('`group`')->set(['`name`' => 'A'])->exec());
        $groupId = self::$_db->lastId();
        $this->assertSame('1', $groupId);
        $this->assertSame('1', self::$_db->insert('class')->set(['name' => 'A'])->exec());
        $classId = self::$_db->lastId();
        $rst     = self::$_db->insert('user')->set(['age' => 1, 'name' => 'user_1', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        $rst     = self::$_db->insert('user')->set(['age' => 2, 'name' => 'user_2', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();

        $this->assertSame('2', self::$_db->insert('`group`')->set(['name' => 'B'])->exec());
        $groupId = self::$_db->lastId();
        $this->assertSame('2', $groupId);
        $this->assertSame('2', self::$_db->insert('class')->set(['name' => 'B'])->exec());
        $classId = self::$_db->lastId();
        $rst     = self::$_db->insert('user')->set(['age' => 3, 'name' => 'user_3', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        $rst     = self::$_db->insert('user')->set(['age' => 4, 'name' => 'user_4', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();

        $this->assertSame('3', self::$_db->insert('`group`')->set(['name' => 'C'])->exec());
        $groupId = self::$_db->lastId();
        $this->assertSame('3', $groupId);
        $this->assertSame('3', self::$_db->insert('class')->set(['name' => 'C'])->exec());
        $classId = self::$_db->lastId();
        $this->assertSame('3', $classId);
        $rst = self::$_db->insert('user')->set(['age' => 5, 'name' => 'user_5', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        $rst = self::$_db->insert('user')->set(['age' => 6, 'name' => 'user_6', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();

        $classId++;
        $rst = self::$_db->insert('user')->set(['age' => 7, 'name' => 'user_7', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();

        $rst = self::$_db->sql('SELECT * FROM `user` WHERE 1;')->fetchAll();
        $this->assertSame(7, count($rst));
    }

    public function test_select_fetch()
    {
        $rst = self::$_db->select()->from('user')->where([':id|>' => 'id_begin'])->fetchAll(['id_begin' => 2]);
        $this->assertSame(5, count($rst));
        $rst = self::$_db->select(['id', 'age', 'name'])->from('user')->where(['id|>' => 1])->fetchRow();
        $this->assertSame([
            'id'   => '2',
            'age'  => '2',
            'name' => 'user_2',
        ], $rst);
        $rst = self::$_db->select('ctime')->from('user')->where(['id|>' => 1])->fetchValue();
        $this->assertSame(date('Y-m-d H:i:s', strtotime($rst)), $rst);
        $rst = self::$_db->select()->from('user')->where(['id|>' => 1])->limit(3, 1)->fetchAll();
        $this->assertSame(3, count($rst));
        $rst = self::$_db->select()->from('user', 'u')
            ->Join('`group`', 'id', 'u', '`group`', 'g')
            ->leftJoin('class', 'id', 'u', 'class', 'c')
            ->where([':u.id|>' => 'id_begin', 'g.id|in' => [2, 3]])->fetchAll(['id_begin' => 1]);
        $this->assertSame(5, count($rst));
        $rst = self::$_db->select()->from('user as u')
            ->Join('`group`', 'id', 'u', '`group`', 'g')
            ->Join('class', 'id', 'u', 'class', 'c', 'k1', 'RIGHT JOIN')
            ->where([':u.id|>' => 'id_begin', ':g.id|in' => ['min', 'max']])->fetchAll(['id_begin' => 1, 'min' => 2, 'max' => 3]);
        $this->assertSame(4, count($rst));
        $rst = self::$_db->select(['sum(age) as sage', 'g.id as gid', 'c.id as cid'])->from('user as u')
            ->Join('`group`', 'id', 'u', '`group`', 'g')
            ->Join('class', 'id', 'u', 'class', 'c', 'k1', 'RIGHT JOIN')
            ->where([':u.id|>' => 'id_begin', 'g.id|in' => [2, 3]])
            ->groupBy('g.id, c.id')
            ->fetchAll(['id_begin' => 1]);
        $this->assertSame(2, count($rst));
        $rst = self::$_db->select(['sum(age) as sage', 'g.id as gid', 'c.id as cid'])->from('user as u')
            ->Join('`group`', 'id', 'u', '`group`', 'g')
            ->Join('class', 'id', 'u', 'class', 'c', 'k1', 'RIGHT JOIN')
            ->where([':u.id|>' => 'id_begin', 'g.id|in' => [2, 3]])
            ->groupBy('g.id, c.id')
            ->having(['sum(age)|>' => 10])
            ->forUpdate()
            ->fetchAll(['id_begin' => 1]);
        $this->assertSame(1, count($rst));
    }

    public function test_yield()
    {
        $count = self::$_db->sql('SELECT COUNT(1) FROM `group`')->fetchValue();
        $rst   = self::$_db->select()->from('`group`')->yield();

        $data = [];
        foreach ($rst as $v) {
            $data[] = $v;
        }
        $this->assertSame((int) $count, count($data));
    }

    public function test_update()
    {
        $this->assertSame('4', self::$_db->insert('class')->set(['name' => 'TEMP'])->exec());
        $id = self::$_db->lastId();
        $this->assertSame(1, self::$_db->update('class')->set(['name' => 'D'])->where(['id' => $id])->exec());
        $this->assertSame(0, self::$_db->update('class')->set(['name' => 'D'])->where(['id' => $id])->exec());
        $this->assertSame($id, self::$_db->select('id')->from('class')->where(['name' => 'D'])->fetchValue());
    }

    public function test_delete()
    {
        $count = self::$_db->select('count(1)')->from('user')->fetchValue();
        $this->assertSame(3, self::$_db->delete('user')->where(['id|<=' => 3])->exec());
        $this->assertSame(3, $count - self::$_db->select('count(1)')->from('user')->fetchValue());
    }

    public function test_page()
    {
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                $total = 0;
                self::$_db->select('id')->from('user')->getPageData(0, $total, 1);
            }
        );
        $allData   = self::$_db->select('id')->from('user')->fetchAll();
        $totalReal = count($allData);
        $totalRst  = 0;

        $rst       = self::$_db->select('id')->from('user')->getPageData(2, $totalRst, 1);
        $this->assertSame($rst, [$allData[0], $allData[1]]);
        $this->assertSame($totalRst, $totalReal);
        $rst = self::$_db->select('id')->from('user')->getPageData(2, $totalRst, 2);
        $this->assertSame($rst, [$allData[2], $allData[3]]);
        $this->assertSame($totalRst, $totalReal);
    }

    public function test_transaction()
    {
        $date = date('Y-m-d H:i:s');

        self::$_db->begin();
        self::$_db->insert('`group`')->set(['`name`' => 'Z'])->exec();
        $groupId = self::$_db->lastId();
        self::$_db->insert('class')->set(['name' => 'Z'])->exec();
        $classId = self::$_db->lastId();
        self::$_db->insert('user')->set(['age' => 1, 'name' => 'user_1', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        self::$_db->insert('user')->set(['age' => 2, 'name' => 'user_2', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        $this->assertSame(self::$_db->select('count(1)')->from('class')->where(['id'=>$classId])->fetchValue(), '1');
        $this->assertSame(self::$_db->select('count(1)')->from('`group`')->where(['id'=>$groupId])->fetchValue(), '1');
        $this->assertSame(self::$_db->select('count(1)')->from('user')->where(['class'=>$classId])->fetchValue(), '2');
        self::$_db->rollback();
        $this->assertSame(self::$_db->select('count(1)')->from('user')->where(['class'=>$classId])->fetchValue(), '0');
        $this->assertSame(self::$_db->select('count(1)')->from('`group`')->where(['id'=>$groupId])->fetchValue(), '0');
        $this->assertSame(self::$_db->select('count(1)')->from('class')->where(['id'=>$classId])->fetchValue(), '0');

        self::$_db->begin();
        self::$_db->insert('`group`')->set(['`name`' => 'Z'])->exec();
        $groupId = self::$_db->lastId();
        self::$_db->insert('class')->set(['name' => 'Z'])->exec();
        $classId = self::$_db->lastId();
        self::$_db->insert('user')->set(['age' => 1, 'name' => 'user_1', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        self::$_db->insert('user')->set(['age' => 2, 'name' => 'user_2', '`group`' => $groupId, 'class' => $classId, 'ctime' => $date])->exec();
        self::$_db->commit();
        $this->assertSame(self::$_db->select('count(1)')->from('class')->where(['id'=>$classId])->fetchValue(), '1');
        $this->assertSame(self::$_db->select('count(1)')->from('`group`')->where(['id'=>$groupId])->fetchValue(), '1');
        $this->assertSame(self::$_db->select('count(1)')->from('user')->where(['class'=>$classId])->fetchValue(), '2');
    }
}
