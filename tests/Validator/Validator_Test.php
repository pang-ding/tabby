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

use Tabby\Test\TestCase;
use Tabby\Validator\Rules;
use Tabby\Validator\Validator;

class Validator_Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Validator::setExecuter(\MyValiExecuter::class);
        Validator::setFormator(\MyValiFormator::class);
    }

    public function test_assert()
    {
        // 测试 registTypeAssert use $return 在外面改返回结果
        $return = 1;
        Validator::registTypeAssert(
            'typeSetValueResetTypeToStrAndReturn',
            function (&$args) use (&$return) {
                // 测试 type类型变更 (走formator::str)
                $args['type'] = 'str';
                // 测试 类型参数赋值
                $args['value'] = $args['typeArgs'];
                return $return;
            }
        );

        // 类型检查失败
        $return = ['k' => 'v'];
        $v = 'a';
        $this->assertSame(Validator::assert($v, 'typeSetValueResetTypeToStrAndReturn:test|max:3'), ['str', ['k' => 'v']]);
        $this->assertSame($v, 'test');

        // 类型返回 false 跳过后续assert
        $return = false;
        $this->assertSame(Validator::assert($v, 'typeSetValueResetTypeToStrAndReturn:test|max:3'), true);

        // 类型返回 true 执行后续assert
        //      后续assert失败
        $return = true;
        $this->assertSame(Validator::assert($v, 'typeSetValueResetTypeToStrAndReturn:test|max:3'), ['str_max', ['max' => 3]]);
        //      后续assert成功
        //              assert返回 true
        $this->assertSame(Validator::assert($v, 'typeSetValueResetTypeToStrAndReturn:test|max:4'), true);
        $this->assertSame(Validator::assert($v, 'typeSetValueResetTypeToStrAndReturn:test|returnTrue|max:3'), ['str_max', ['max' => 3]]);
        //              assert返回 false 跳过后续检查
        $this->assertSame(Validator::assert($v, 'typeSetValueResetTypeToStrAndReturn:test|returnFalse|max:3'), true);

        // assert 读 assertArgs 赋值
        $v = '1';
        $this->assertSame(Validator::assert($v, 'int|min:1|setValueByArgs:2'), true);
        $this->assertSame($v, 2);

        // assert 读 typeArgs 赋值
        $v = '1';
        $this->assertSame(Validator::assert($v, 'int:2|setValueByTypeArgs'), true);
        $this->assertSame($v, 2);
    }

    public function test_assert_array()
    {
        // 数组成功
        $v = [1, '2', 3];
        $this->assertSame(Validator::assert($v, 'int|min:1', Rules::normalizeFlag('arr')), true);
        $this->assertSame($v, [1, 2, 3]);

        // 数组失败
        $v = ['2', 1, '3'];
        $this->assertSame(Validator::assert($v, 'int|min:2', Rules::normalizeFlag('arr')), ['int_min', ['min' => 2]]);
        $this->assertSame($v, [2, 1, '3']);
    }

    public function test_flag()
    {
        // arr 单独做了测试 test_assert_array

        // notrim 不做trim处理
        $v = ' a ';
        $this->assertSame(Validator::assert($v, 'str|max:3', Validator::FLAG_OFF_TRIM), true);
        $this->assertSame($v, ' a ');
        $this->assertSame(Validator::assert($v, 'str|max:3'), true);
        $this->assertSame($v, 'a');

        $v = '';
        // ept 允许为空 有默认值
        $this->assertSame(Validator::assert($v, 'str|min:3', Validator::FLAG_EMPTY, 'default'), true);
        $this->assertSame($v, 'default');

        // ept 允许为空 没默认值
        $v = '';
        $this->assertSame(Validator::assert($v, 'str|min:3', Validator::FLAG_EMPTY), true);
        $this->assertSame($v, '');

        // ept 允许为空 有默认值 value=null
        $v = null;
        $this->assertSame(Validator::assert($v, 'str|min:3', Validator::FLAG_EMPTY, 'default'), true);
        $this->assertSame($v, 'default');

        $v = null;
        // null 允许为 null 没默认值
        $this->assertSame(Validator::assert($v, 'str|min:3', Validator::FLAG_NULL), true);
        $this->assertSame($v, null);

        // null 允许为 null 有默认值
        $this->assertSame(Validator::assert($v, 'str|min:3', Validator::FLAG_NULL, 'default'), true);
        $this->assertSame($v, 'default');

        // noxss 禁用XSS替换(htmlspecialchars)
        $v = "\"'<>&";
        $this->assertSame(Validator::assert($v, 'str', Validator::FLAG_OFF_XSS), true);
        $this->assertSame($v, "\"'<>&");

        // nofmt 禁用除trim以外的全部格式化操作(包含xss)
        $v = "\"'<>&";
        $this->assertSame(Validator::assert($v, 'str', Validator::FLAG_OFF_FORMAT), true);
        $this->assertSame($v, "\"'<>&");

        // XSS替换
        $v = "\"'<>&";
        $this->assertSame(Validator::assert($v, 'str'), true);
        $this->assertSame($v, '&quot;&#039;&lt;&gt;&amp;');

        $v = hex2bin('80'); // 不在 ascii 和 utf8 范围的东西
        $this->assertSame(Validator::assert($v, 'str'), ['str_broken', null]);
    }

    public function test_datetime()
    {
        $v = '2020-02-02';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d'), true);
        $this->assertSame($v, '2020-02-02');

        // 格式错误
        $v = '2020-01-32';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d'), ['datetime', ['example' => date('Y-m-d')]]);
        $v = '2020-02-02 01:01:01';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d'), ['datetime', ['example' => date('Y-m-d')]]);

        // 格式化加号解决同时支持 2020-02-02 | 2020-02-02 01:01:01 两种格式问题
        // format格式化
        $v = '2020-02-02 01:01:01';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d+'), true);
        $this->assertSame($v, '2020-02-02');
        // 多余后缀处理
        $v = '2020-02-02 01:01:01111';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d+'), true);
        $this->assertSame($v, '2020-02-02');
        $v = '2020-02-02 01:01:01111';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d H:i:s+'), true);
        $this->assertSame($v, '2020-02-02 01:01:01');

        // min
        $v = '2020-02-02';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|min:2020-02-02'), true);
        $this->assertSame($v, '2020-02-02');
        $v = '2020-02-02';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|min:2020-02-03'), ['datetime_min', ['min' => '2020-02-03']]);
        $this->assertSame($v instanceof \DateTime, true);
        $v = '2020-02-02 01:00:00';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d H:i:s|min:2020-02-02 01:00:01'), ['datetime_min', ['min' => '2020-02-02 01:00:01']]);
        $this->assertSame($v instanceof \DateTime, true);

        // max
        $v = '2020-02-02';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|max:2020-02-02'), true);
        $this->assertSame($v, '2020-02-02');
        // 按格式化后比较
        $v = '2020-02-02 01:00:00';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d+|max:2020-02-02'), true);
        $v = '2020-02-02';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|max:2020-02-01'), ['datetime_max', ['max' => '2020-02-01']]);
        $this->assertSame($v instanceof \DateTime, true);
        $v = '2020-02-02 01:00:00';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d H:i:s|max:2020-02-02 00:59:00'), ['datetime_max', ['max' => '2020-02-02 00:59:00']]);
        $this->assertSame($v instanceof \DateTime, true);

        // between
        $v = '2020-02-01';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|between:2020-02-01,2020-02-03'), true);
        $v = '2020-02-02';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|between:2020-02-01,2020-02-03'), true);
        $v = '2020-02-03';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|between:2020-02-01,2020-02-03'), true);
        $v = '2020-02-01';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|between:2020-02-01,2020-02-03'), true);
        $v = '2020-01-01';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|between:2020-02-01,2020-02-03'), ['datetime_between', ['min' => '2020-02-01', 'max' => '2020-02-03']]);
        $v = '2020-02-04';
        $this->assertSame(Validator::assert($v, 'datetime:Y-m-d|between:2020-02-01,2020-02-03'), ['datetime_between', ['min' => '2020-02-01', 'max' => '2020-02-03']]);
    }

    // todo: formator & flag引用改变
}
