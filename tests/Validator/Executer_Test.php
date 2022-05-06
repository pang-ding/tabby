<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Validator;

use Consts\TabbyConsts;
use Mod\ClassMod;
use Tabby\Framework\DI;
use Tabby\Test\Store\Mysql\Tables;
use Tabby\Test\TestCase;
use Tabby\Validator\Executer;
use Tabby\Validator\Rules;

class Executer_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        DI::DB()->sql(Tables::$table_class)->exec();
    }

    public function test_str()
    {
        $this->assertType('str', ' 0', '0', true);
        $this->assertType('str', '0 ', '0 ', true, null, null, 'ept.notrim');
        $this->assertType('str', ' ', '', false, null, null, 'ept');
        $this->assertType('str', ' ', ' ', true, null, null, 'ept.notrim');
        $this->assertType('str', [], [], null);
        $this->assertType('str', [], [], null, null, null, 'ept');
        $this->assertType('str', null, 'aaa', false, 'aaa');
        $this->assertType('str', null, 'aaa', false, 'aaa', 'ept');
        $this->assertType('str', 0, '', true);
        $this->assertType('str', 0, '', false, null, null, 'ept');
        $this->assertType('str', false, '', false, null, null, 'ept');
        $this->assertType('str', null, '', true);
        $this->assertType('str', '123', '123', true);
        $this->assertType('str', 123, '123', true);
        $this->assertType('str', true, '1', true);
        $this->assertType('str', 123.456, '123.456', true);
        $this->assertType('str', 0.0, '', true);
        $this->assertType('str', 0.0, 'test', false, 'test');
        $this->assertType('str', [1], [1], null);
    }

    public function test_int()
    {
        $this->assertType('int', [], [], null);
        $this->assertType('int', [], [], null, null, null, 'ept');
        $this->assertType('int', '0', 0, false, null, null, 'ept');
        $this->assertType('int', '0', 0, true);
        $this->assertType('int', false, 0, true);
        $this->assertType('int', '', 0, true);
        $this->assertType('int', null, 0, true);
        $this->assertType('int', '0', 10, false, 10);
        $this->assertType('int', PHP_INT_MAX, PHP_INT_MAX, true);
        $this->assertType('int', 123, 123, true);
        $this->assertType('int', PHP_INT_MIN, PHP_INT_MIN, true);
        $this->assertType('int', '123', 123, true);
        $this->assertType('int', '123', 123, true);
        $this->assertType('int', '123X', '123X', null);
        $a = PHP_INT_MAX + 1;
        $this->assertType('int', $a, $a, null);
        $this->assertType('int', 123.321, 123.321, null);
    }

    public function test_float()
    {
        $this->assertType('float', [], [], null);
        $this->assertType('float', [], [], null, null, null, 'ept');
        $this->assertType('float', '0', 0, false, null, null, 'ept');
        $this->assertType('float', '0', 0, true);
        $this->assertType('float', false, 0, true);
        $this->assertType('float', '', 0, true);
        $this->assertType('float', null, 0, true);
        $this->assertType('float', null, 1.23, false, 1.23456, '2');
        $this->assertType('float', null, 1.23456, false, 1.23456);
        $this->assertType('float', PHP_INT_MAX, (float) PHP_INT_MAX, true);
        $this->assertType('float', 123, (float) 123, true);
        $this->assertType('float', PHP_INT_MIN, (float) PHP_INT_MIN, true);
        $this->assertType('float', 9999999999999999999999999999999999, (float) 9999999999999999999999999999999999, true);
        $this->assertType('float', '123', (float) 123, true);
        $this->assertType('float', '123', (float) 123, true);
        $this->assertType('float', '123X', '123X', null);
        $this->assertType('float', 123.321, 123.32, true, null, '2');
        $this->assertType('float', 123.321, 123.321, true);
    }

    public function test_datetime()
    {
        // 逻辑:
        // 默认值只能是日期格式, 空字符串等同于没设置
        // target等于 'defept' 时会换成  TabbyConsts::DATETIME_VALUE_EMPTY
        $this->assertTypeDataTime('', 'Y-m-d', false, '', 'ept', 'defept');

        // 值为空情况
        //      有默认值        => value:默认值 & return:false
        $this->assertTypeDataTime('', 'Y-m-d', false, '2020-02-03', 0, date_create_from_format('Y-m-d', '2020-02-03')->getTimestamp());
        //      没有默认值
        //          允许为空    => value:'' & return:false
        $this->assertTypeDataTime('', 'Y-m-d', false, null, 'ept', 'defept');
        //          不许为空    => value:不变 & return:['example' => ]
        $this->assertTypeDataTime('', 'Y-m-d', ['example' => date('Y-m-d')]);
        $this->assertTypeDataTime('', 'Y m d+', ['example' => date('Y m d')]);
        // 有值情况
        //      格式化成功      => value:DateTime & return:true
        $this->assertTypeDataTime('2020-02-03', 'Y-m-d', true);
        $this->assertTypeDataTime('2020-02-03', ' Y-m-d', true);
        $this->assertTypeDataTime('2020-02-03 01:01:01', 'Y-m-d+', true, null, 0, date_create_from_format('Y-m-d', '2020-02-03')->getTimestamp());
        //      格式化失败      => value:不变 & return:['example' => ]
        $this->assertTypeDataTime('202002-03 01:01:01', 'Y-m-d+', ['example' => date('Y-m-d')]);
        $this->assertTypeDataTime('202002-03 01:01:01', ' Y-m-d+ ', ['example' => date('Y-m-d')]);

        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                $this->assertTypeDataTime('2020-02-03', '', true);
            }
        );
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                $this->assertTypeDataTime('', '', ['example' => date('Y-m-d')]);
            }
        );
    }

    public function test_other()
    {
        $value = '';
        $default = null;
        $flag = 0;
        $typeArgs = '';
        $args = [
            'value' => &$value,
            'flag' => $flag,
            'typeArgs' => &$typeArgs,
            'default' => &$default,
        ];
        $this->assertSame(Executer::other($args), true);
    }

    public function test_str_min()
    {
        $this->assertByName('str_min', 'aaa', '3', true);
        $this->assertByName('str_min', 'aaaa', '3', true);
        $this->assertByName('str_min', 'aa', '3', ['min' => 3]);
        $this->assertByName('str_min', '字字字字', '3', true);
        $this->assertByName('str_min', '字字', '3', ['min' => 3]);
    }

    public function test_str_max()
    {
        $this->assertByName('str_max', 'aaa', '3', true);
        $this->assertByName('str_max', 'aaaa', '3', ['max' => 3]);
        $this->assertByName('str_max', 'aa', '3', true);
        $this->assertByName('str_max', '字字字字', '3', ['max' => 3]);
        $this->assertByName('str_max', '字字', '3', true);
    }

    public function test_str_between()
    {
        $this->assertByName('str_between', 'aa', '3,5', ['min' => 3, 'max' => 5]);
        $this->assertByName('str_between', 'aaa', '3,5', true);
        $this->assertByName('str_between', 'aaaa', '3,5', true);
        $this->assertByName('str_between', 'aaaaa', '3,5', true);
        $this->assertByName('str_between', 'aaaaaa', '3,5', ['min' => 3, 'max' => 5]);
        $this->assertByName('str_between', '字字', '3,5', ['min' => 3, 'max' => 5]);
        $this->assertByName('str_between', '字字字字', '3,5', true);
    }

    public function test_str_len()
    {
        $this->assertByName('str_len', 'aaa', '3', true);
        $this->assertByName('str_len', 'aaaa', '3', ['len' => 3]);
        $this->assertByName('str_len', '字字字', '3', true);
    }

    public function test_str_in()
    {
        $this->assertByName('str_in', 'aa', 'aa|bb', true);
        $this->assertByName('str_in', 'bb', 'aa|bb', true);
        $this->assertByName('str_in', 'a', 'aa|bb', null);
        $this->assertByName('str_in', 'aa', 'aa', true);
    }

    public function test_str_regex()
    {
        $this->assertByName('str_regex', '13812345678', '/^1[0-9]{10}$/', true);
        $this->assertByName('str_regex', '138123456789', '/^1[0-9]{10}$/', null);
        $this->assertByName('str_regex', '1X8123456789', '/^1[0-9]{10}$/', null);
    }

    public function test_str_hasid()
    {
        ClassMod::insert(['name' => 'A']);
        $id = ClassMod::insert(['name' => 'B']);
        $this->assertByName('str_hasid', $id, '\Mod\ClassMod', true);
        $this->assertByName('str_hasid', "{$id}", '\Mod\ClassMod', true);
        $this->assertByName('str_hasid', $id + 1, '\Mod\ClassMod', null);
    }

    public function test_str_mobile()
    {
        $this->assertByName('str_mobile', '13812345678', null, true);
        $this->assertByName('str_mobile', '138123456789', null, null);
        $this->assertByName('str_mobile', '1X8123456789', null, null);
    }

    public function test_str_email()
    {
        $this->assertByName('str_email', '123@gmail.com', null, true);
        $this->assertByName('str_email', '123@gmail.com', null, true);
        $this->assertByName('str_email', '12-_.3@gmail.gmail.com', null, true);
        $this->assertByName('str_email', '@gmail.com', null, null);
        $this->assertByName('str_email', '"@gmail.com', null, null);
        $this->assertByName('str_email', '"aaa@gmail.com', null, null);
        $this->assertByName('str_email', '\'asd@gmail.com', null, null);
        $this->assertByName('str_email', '^@gmail.com', null, null);
        $this->assertByName('str_email', '1X8123456789', null, null);
    }

    public function test_int_min()
    {
        $this->assertByName('int_min', 1, '1', true);
        $this->assertByName('int_min', 1, '2', ['min' => 2]);
    }

    public function test_int_max()
    {
        $this->assertByName('int_max', 1, '1', true);
        $this->assertByName('int_max', 3, '2', ['max' => 2]);
    }

    public function test_int_between()
    {
        $this->assertByName('int_between', 1, '2,4', ['min' => 2, 'max' => 4]);
        $this->assertByName('int_between', 2, '2,4', true);
        $this->assertByName('int_between', 3, '2,4', true);
        $this->assertByName('int_between', 4, '2,4', true);
        $this->assertByName('int_between', 5, '2,4', ['min' => 2, 'max' => 4]);
    }

    public function test_float_min()
    {
        $this->assertByName('float_min', 1.1, '1.1', true);
        $this->assertByName('float_min', 1.09, '1.1', ['min' => 1.1]);
    }

    public function test_float_max()
    {
        $this->assertByName('float_max', 1.1, '1.1', true);
        $this->assertByName('float_max', 1.11, '1.1', ['max' => 1.1]);
    }

    public function test_float_between()
    {
        $flag = 0;
        $this->assertByName('float_between', 1.1, '1.2,1.4', ['min' => 1.2, 'max' => 1.4], $flag, '1');
        $this->assertByName('float_between', 1.2, '1.2,1.4', true, $flag, '1');
        $this->assertByName('float_between', 1.3, '1.2,1.4', true, $flag, '1');
        $this->assertByName('float_between', 1.4, '1.2,1.4', true, $flag, '1');
        $this->assertByName('float_between', 1.5, '1.2,1.4', ['min' => 1.2, 'max' => 1.4], $flag, '1');
    }

    public function test_int_in()
    {
        $this->assertByName('int_in', 2, '2|3', true);
        $this->assertByName('int_in', 3, '2|3', true);
        $this->assertByName('int_in', 1, '2|3', null);
        $this->assertByName('int_in', 2, '2', true);
        $this->assertByName('int_in', 0, '|3', true);
        $this->assertByName('int_in', 1, '|3', null);
        $this->assertByName('int_in', 23, '123|3', null);
        $this->assertByName('int_in', 23, '234|3', null);
        $this->assertByName('int_in', 23, '23.1|3', null);
        $this->assertByName('int_in', 23, '23.0|3', true);
        $this->assertByName('int_in', 1, '0|3', null);
        $this->assertByName('int_in', 0, '0', true);
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                $this->assertByName('int_in', 0, '', true);
            }
        );
    }

    public function test_int_hasid()
    {
        ClassMod::insert(['name' => 'INT_A']);
        $id = ClassMod::insert(['name' => 'INT_B']);
        $this->assertByName('int_hasid', $id, '\Mod\ClassMod', true);
        $this->assertByName('int_hasid', "{$id}", '\Mod\ClassMod', true);
        $this->assertByName('int_hasid', $id + 1, '\Mod\ClassMod', null);
    }

    // datetime 放在 Validator_Test 做

    protected function assertByName($name, $value, $assertArgs, $rst, &$flag = 0, $typeArgs = '')
    {
        $args = [
            'value' => &$value,
            'flag' => Rules::normalizeFlag($flag),
            'assertArgs' => &$assertArgs,
            'typeArgs' => &$typeArgs,
        ];
        $this->assertSame(Executer::$name($args), $rst);
    }

    protected function assertTypeDataTime($value, $format, $rst, $default = null, $flag = 0, $targetReal = null)
    {
        $target = $value;
        $args = [
            'value' => &$value,
            'flag' => Rules::normalizeFlag($flag),
            'typeArgs' => &$format,
            'default' => &$default,
        ];
        $this->assertSame(Executer::datetime($args), $rst);
        if (is_bool($rst)) {
            if ($targetReal !== null) {
                $target = $targetReal === 'defept' ? TabbyConsts::DATETIME_VALUE_EMPTY : $targetReal;
            } else {
                $target = date_create_from_format($format, $target)->getTimestamp();
            }
            if ($value instanceof \DateTime) {
                $this->assertSame($value->getTimestamp(), $target);
            } else {
                $this->assertSame($value, $target);
            }
        } else {
            $this->assertSame($value, $target);
        }
    }

    protected function assertType($name, $value, $target, $rst, $default = null, $typeArgs = null, $flag = 0)
    {
        $args = [
            'value' => &$value,
            'flag' => Rules::normalizeFlag($flag),
            'typeArgs' => &$typeArgs,
            'default' => &$default,
        ];
        $this->assertSame(Executer::$name($args), $rst);
        $this->assertSame($value, $target);
    }
}
