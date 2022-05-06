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

use Mod\ArticleMod;
use Mod\MongoTestMod;
use Consts\TabbyConsts;
use Tabby\Test\TestCase;
use Tabby\Validator\Data;

class MongoMod_Test extends TestCase
{
    protected static $_DB;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        static::$_DB = \Tabby\Tabby::$DI::Mongo();
        ArticleMod::getCollection()->drop();
        MongoTestMod::getCollection()->drop();
        static::$_DB->{ArticleMod::SEQ_COLLECTION}->drop();
    }

    public function test_sequence()
    {
        $this->assertSame(ArticleMod::sequence(), 1);
        $this->assertSame(MongoTestMod::sequence(), 1);
        $this->assertSame(ArticleMod::sequence(), 2);
        $this->assertSame(MongoTestMod::sequence(), 2);
        $this->assertSame(ArticleMod::sequence(), 3);
        $this->assertSame(MongoTestMod::sequence(), 3);
    }

    public function test_insertOne_getById()
    {
        $irst = ArticleMod::insertOne(['name' => 'tabby', 'age' => 10]);
        $frst = ArticleMod::find(['name'=>'tabby'])->toArray();
        $this->assertSame($irst->getInsertedCount(), 1);
        $id = $irst->getInsertedId();
        $this->assertSame($id . '', $frst[0]['_id'] . '');
        $this->assertSame(ArticleMod::getById($id)->name, 'tabby');

        $irst = MongoTestMod::insertOne(['_id'=>MongoTestMod::sequence(), 'name' => 'tabby', 'age' => 10]);
        $frst = MongoTestMod::find(['name'=>'tabby'])->toArray();
        $this->assertSame($irst->getInsertedCount(), 1);
        $id = $irst->getInsertedId();
        $this->assertSame($id, $frst[0]['_id']);
        $this->assertSame(is_int($id), true);
        $this->assertSame(MongoTestMod::getById($id)->name, 'tabby');
        $this->assertSame(count(MongoTestMod::getById($id, ['_id'=>1])), 1);
    }

    public function test_hasId()
    {
        $id = MongoTestMod::insert(['_id'=>MongoTestMod::sequence(), 'name' => 'test_hasId', 'age' => 10]);
        $this->assertSame(MongoTestMod::hasId($id), true);
        $this->assertSame(MongoTestMod::hasId(10000), false);
    }

    public function test_getValue()
    {
        MongoTestMod::insert(['_id'=>MongoTestMod::sequence(), 'name' => 'test_getValue', 'age' => 99]);
        $this->assertSame(MongoTestMod::getValue('age', ['name'=>'test_getValue']), 99);
        $this->assertSame(MongoTestMod::getValue('age2', ['name'=>'test_getValue']), null);
        $this->assertSame(MongoTestMod::getValue('age', ['name'=>'test_getValue1']), null);
    }

    public function test_insertMany()
    {
        $irst = ArticleMod::insertMany([['name' => 'tom', 'age' => 20], ['name' => 'jerry', 'age' => 30]]);
        $this->assertSame($irst->getInsertedCount(), 2);
        $irst = MongoTestMod::insertMany([['_id'=>MongoTestMod::sequence(), 'name' => 'tom', 'age' => 20], ['_id'=>MongoTestMod::sequence(), 'name' => 'jerry', 'age' => 30]]);
        $this->assertSame($irst->getInsertedCount(), 2);
    }

    public function test_insert()
    {
        $irst = MongoTestMod::insert(['_id'=>MongoTestMod::sequence(), 'name' => 'CrayonShinChan', 'age' => 5]);
        $frst = MongoTestMod::find(['name'=>'CrayonShinChan'])->toArray();
        $this->assertSame($irst, $frst[0]['_id']);
    }

    public function test_list()
    {
        $this->assertSame(count((array) MongoTestMod::list(['name'=>1], ['_id'=>['$gt'=>5, '$lt'=>8]])), 2);
    }

    public function test_updateById()
    {
        $id = ArticleMod::insert(['name' => 'test_updateById', 'age' => 10]);
        $this->assertSame(ArticleMod::updateById($id, ['name' => 'test_updateById2', 'age' => 20]), 1);
        $frst = ArticleMod::find(['name'=>'test_updateById2'])->toArray();
        $this->assertSame($frst[0]['age'], 20);
    }

    public function test_update()
    {
        $this->assertSame(ArticleMod::update(['age' => ['$gt'=>10]], ['age' => 10]) > 1, true);
        $frst = ArticleMod::find(['age' => ['$gt'=>10]])->toArray();
        $this->assertSame(count($frst), 0);
    }

    public function test_pageList()
    {
        $data = [];
        for ($i=1; $i <= 100; $i++) {
            $data[] = ['name' => 'test_pageList' . $i, 'age' => 199];
        }
        ArticleMod::insertMany($data);
        $rst  = ArticleMod::pageList(20, 2, ['_id'=>-1], ['age' => 199]);
        $this->assertSame($rst[TabbyConsts::MOD_PAGE_TOTAL_FIELD], 100);
        $this->assertSame(count($rst[TabbyConsts::MOD_PAGE_LIST_FIELD]), 20);
    }

    public function test_transaction()
    {
        ArticleMod::startTransaction();

        ArticleMod::insert(['name' => 'test_transaction', 'age' => 10]);
        MongoTestMod::insert(['name' => 'test_transaction', 'age' => 10]);
        $rst = ArticleMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst['age'], 10);
        $rst = MongoTestMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst['age'], 10);
        ArticleMod::abortTransaction();
        $rst = ArticleMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst, null);
        $rst = MongoTestMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst, null);

        ArticleMod::startTransaction();

        ArticleMod::insert(['name' => 'test_transaction', 'age' => 10]);
        MongoTestMod::insert(['name' => 'test_transaction', 'age' => 10]);
        $rst = ArticleMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst['age'], 10);
        $rst = MongoTestMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst['age'], 10);
        $this->assertSame(ArticleMod::update(['name' => 'test_transaction'], ['age' => 20]) === 1, true);
        ArticleMod::commitTransaction();
        $rst = ArticleMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst['age'], 20);
        $rst = MongoTestMod::findOne(['name'=>'test_transaction']);
        $this->assertSame($rst['age'], 10);
    }
}
