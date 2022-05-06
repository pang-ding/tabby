<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Mod;

use Mod\ClassMod;
use Mod\GroupMod;
use Mod\UserMod;
use Tabby\Framework\DI;
use Tabby\Test\Store\Mysql\Tables;
use Tabby\Test\TestCase;

class Mod_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        DI::DB()->sql(Tables::$table_user)->exec();
        DI::DB()->sql(Tables::$table_group)->exec();
        DI::DB()->sql(Tables::$table_class)->exec();
    }

    public function test_get_table_name()
    {
        $this->assertSame(static::getValue(UserMod::class, '_TABLE_NAME'), UserMod::getTableName());
    }

    public function test_insert()
    {
        $this->assertSame(ClassMod::insert(['name' => 'class1']), '1');
        $this->assertSame(ClassMod::insert(['name' => 'class2']), '2');
        $this->assertSame(ClassMod::insert(['name' => 'class3']), '3');
        $this->assertSame(GroupMod::insert(['name' => 'group1']), '1');
        $this->assertSame(GroupMod::insert(['name' => 'group2']), '2');
        $this->assertSame(GroupMod::insert(['name' => 'group3']), '3');
        $this->assertSame(UserMod::insert([
            'age'     => '11',
            'name'    => 'user1',
            '`group`' => '1',
            'class'   => '1',
        ]), '1');
        $this->assertSame(UserMod::insert([
            'age'     => '22',
            'name'    => 'user2',
            '`group`' => '2',
            'class'   => '2',
        ]), '2');
        $this->assertSame(UserMod::insert([
            'age'     => '33',
            'name'    => 'user3',
            '`group`' => '3',
            'class'   => '3',
        ]), '3');
        $this->assertSame(UserMod::insert([
            'age'     => '44',
            'name'    => 'user4',
            '`group`' => '3',
            'class'   => '3',
        ]), '4');
    }

    public function test_select()
    {
        $this->assertSame(ClassMod::select('name')->where(['id' => 3])->fetchValue(), 'class3');
    }

    public function test_get_by_id()
    {
        $class3 = ClassMod::getById(3);
        $this->assertSame(isset($class3['name']), true);
        $this->assertSame($class3['name'], 'class3');
        $class3 = ClassMod::getById(3, 'id');
        $this->assertSame(isset($class3['name']), false);
        $this->assertSame($class3['id'], '3');
    }

    public function test_count()
    {
        $this->assertSame(ClassMod::count(''), '3');
        $this->assertSame(ClassMod::count(['id' => 3]), '1');
    }

    public function test_exist()
    {
        $this->assertSame(ClassMod::exist(['id' => 4]), false);
        $this->assertSame(ClassMod::exist(['id' => 3]), true);
    }

    public function test_fetch_value()
    {
        $this->assertSame(ClassMod::fetchValue(['id' => 3], 'name'), 'class3');
    }

    public function test_fetch_row()
    {
        $this->assertSame(ClassMod::fetchRow(['id' => 3], 'name'), ['name' => 'class3']);
    }

    public function test_fetch_all()
    {
        $this->assertSame(ClassMod::fetchAll(['id|<' => 3], 'name'), [['name' => 'class1'], ['name' => 'class2']]);
    }

    public function test_dict()
    {
        $this->assertSame(json_encode(ClassMod::dict('id', 'name', ['id|<' => 3])), '{"1":"class1","2":"class2"}');
        $this->assertSame(json_encode(ClassMod::dict('id', 'name', ['id|<' => 3], 'id DESC')), '{"2":"class2","1":"class1"}');
    }

    public function test_his_id()
    {
        $this->assertSame(ClassMod::hasId(4), false);
        $this->assertSame(ClassMod::hasId(3), true);
    }

    public function test_his_val()
    {
        $this->assertSame(ClassMod::hasVal('name', 'class2', ['id|<' => 3]), true);
        $this->assertSame(ClassMod::hasVal('name', 'class2', ['id|<' => 2]), false);
    }

    public function test_list()
    {
        $data = UserMod::list('id,name', ['id|<' => 4], 'id desc', 2, 1);
        $this->assertSame(count($data), 2);
        $this->assertSame($data[0], ['id' => '2', 'name' => 'user2']);
        $this->assertSame($data[1]['name'], 'user1');
    }

    public function test_page_list()
    {
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                UserMod::pageList(0, 2, 'id,name', ['id|>' => 1], 'id desc');
            }
        );
        $data = UserMod::pageList(2, 2, 'id,name', ['id|>' => 1], 'id desc');
        $this->assertSame($data['total'], 3);
        $this->assertSame(count($data['list']), 1);
        $this->assertSame($data['list'][0], ['id' => '2', 'name' => 'user2']);
    }

    public function test_update()
    {
        $this->assertSame(
            UserMod::update(
                [
                    'id|<' => 3,
                ],
                [
                    '`group`' => '3',
                    'class'   => '3',
                ]
            ),
            2
        );
        $this->assertSame(UserMod::count(['class' => 3]), '4');
        $this->assertSame(UserMod::count(['class' => 1]), '0');
        $this->assertSame(
            UserMod::update(
                [
                    'id|<' => 3,
                ],
                [
                    '`group`' => '1',
                    'class'   => '1',
                ],
                1
            ),
            1
        );
        $this->assertSame(UserMod::count(['class' => 3]), '3');
        $this->assertSame(UserMod::count(['class' => 1]), '1');
    }

    public function test_update_by_id()
    {
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                UserMod::updateById(0, ['class' => '2']);
            }
        );
        $this->assertSame(
            UserMod::updateById(
                2,
                [
                    '`group`' => '2',
                    'class'   => '2',
                ]
            ),
            1
        );
        $this->assertSame(UserMod::count(['class' => 2]), '1');
    }

    public function test_delete()
    {
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                UserMod::delete([]);
            }
        );
        $this->assertSame(
            UserMod::delete(
                ['id|<' => 4]
            ),
            1
        );
        $this->assertSame(UserMod::hasId(1), false);
        $this->assertSame(UserMod::hasId(2), true);
        $this->assertSame(
            UserMod::delete(
                ['id|<' => 4],
                2
            ),
            2
        );
        $this->assertSame(UserMod::hasId(2), false);
        $this->assertSame(UserMod::hasId(3), false);
        $this->assertSame(UserMod::hasId(4), true);
    }

    public function test_delete_by_id()
    {
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                UserMod::deleteById(0);
            }
        );
        $this->assertSame(UserMod::deleteById(3), 0);
        $this->assertSame(UserMod::count([]), '1');
        $this->assertSame(UserMod::deleteById(4), 1);
        $this->assertSame(UserMod::count([]), '0');
    }
}
