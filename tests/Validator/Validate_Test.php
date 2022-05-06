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
use Tabby\Error\ErrorAbstract;
use Tabby\Framework\Language;
use Tabby\Test\TestCase;
use Tabby\Validator\Data;
use Tabby\Validator\Message;
use Tabby\Validator\Validate;
use Tabby\Validator\Validator;

class Validate_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        static::setValue(Validate::class, '_rules', null);
        Validate::init(['a' => 'a']);
    }

    public function test_init()
    {
        static::setValue(Validate::class, '_rules', null);
        Validate::init(['a' => 'aa']);
        // 不能重复设置
        Validate::init(['a' => 'bb']);
        $this->assertSame(static::getValue(Validate::class, '_rules')['a'], 'aa');
    }

    public function test_mergeRules()
    {
        $this->assertSame(static::getValue(Validate::class, '_rules')['a'], 'aa');
        Validate::mergeRules(['a' => 'aaa']);
        $this->assertSame(static::getValue(Validate::class, '_rules')['a'], 'aaa');
    }

    public function test_setExecuter()
    {
        Validate::setExecuter(\MyValiExecuter::class);
        $this->assertSame(static::getValue(Validator::class, '_executer'), \MyValiExecuter::class);
    }

    public function test_setFormator()
    {
        Validate::setFormator(\MyValiFormator::class);
        $this->assertSame(static::getValue(Validator::class, '_formator'), \MyValiFormator::class);
    }

    public function test_assertInputValueByRule()
    {
        $this->assertSame(Validate::assertInputValueByRule(' u1', 'str|min:1', 'user'), 'u1');
        // flag & 默认值
        $this->assertSame(Validate::assertInputValueByRule('', 'str|min:1', 'user', 'ept', 'u1'), 'u1');
        // int flag
        $this->assertSame(Validate::assertInputValueByRule('', 'str|min:1', 'user', Validator::FLAG_EMPTY, 'u1'), 'u1');
        // 异常
        $msg = static::formatMsg('attr1', 'str_min', ['min' => 1], '');
        $this->checkException(
            function () {
                Validate::assertInputValueByRule('', 'str|min:1', 'attr1');
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog($msg, 'str|min:1', '', 'attr1'),
            $msg,
            ['key' => 'attr1']
        );
        // 自定义异常
        $this->checkException(
            function () {
                Validate::assertInputValueByRule('a', 'str|min:2', 'attr1', null, null, 'test_error_msg');
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('test_error_msg', 'str|min:2', 'a', 'attr1'),
            'test_error_msg',
            ['key' => 'attr1']
        );
    }

    public function test_assertSysValueByRule()
    {
        $this->assertSame(Validate::assertSysValueByRule(' u1', 'str|min:1'), 'u1');
        // flag & 默认值
        $this->assertSame(Validate::assertSysValueByRule('', 'str|min:1', 'ept', 'u1'), 'u1');
        // int flag
        $this->assertSame(Validate::assertSysValueByRule('', 'str|min:1', Validator::FLAG_EMPTY, 'u1'), 'u1');
        // 异常
        $this->checkException(
            function () {
                Validate::assertSysValueByRule('', 'str|min:1');
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('', 'str|min:1', '', ''),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );

        // 自定义异常
        $this->checkException(
            function () {
                Validate::assertSysValueByRule('a', 'str|min:2', null, null, 'test_error_msg', 'attr1');
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('test_error_msg', 'str|min:2', 'a', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );
    }

    public function test_assertInputValueByKey()
    {
        Validate::mergeRules(['attr1' => 'str|min:2']);

        $this->assertSame(Validate::assertInputValueByKey(' u1', 'attr1'), 'u1');

        // flag & 默认值
        $this->assertSame(Validate::assertInputValueByKey('', 'attr1.ept', 'u1'), 'u1');

        // 异常
        $msg = static::formatMsg('attr1', 'str_min', ['min' => 2], '');
        $this->checkException(
            function () {
                Validate::assertInputValueByKey('', 'attr1');
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog($msg, 'str|min:2', '', 'attr1'),
            //formatMsg($key, $assertName, $msgAttr, $msg)
            static::formatMsg('attr1', 'str_min', ['min' => 2], $msg),
            ['key' => 'attr1']
        );

        // 自定义异常
        $this->checkException(
            function () {
                Validate::assertInputValueByKey('a', 'attr1', null, 'test_error_msg');
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('test_error_msg', 'str|min:2', 'a', 'attr1'),
            'test_error_msg',
            ['key' => 'attr1']
        );
    }

    public function test_assertSysValueByKey()
    {
        Validate::mergeRules(['attr1' => 'str|min:2']);

        $this->assertSame(Validate::assertSysValueByKey(' u1', 'attr1'), 'u1');

        // flag & 默认值
        $this->assertSame(Validate::assertSysValueByKey('', 'attr1.ept', 'u1'), 'u1');

        // 异常
        $this->checkException(
            function () {
                Validate::assertSysValueByKey('', 'attr1');
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('', 'str|min:2', '', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );

        // 自定义异常
        $this->checkException(
            function () {
                Validate::assertSysValueByKey('a', 'attr1', null);
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('', 'str|min:2', 'a', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );
    }

    public function test_assertInputValue()
    {
        Validate::mergeRules([
            'attr1' => 'str|max:2',
            'attr2' => 'str|min:2',
            'attr3' => 'str|min:2',
        ]);

        // 信任Data不做验证, 'aaa'数据超过max:2
        $data = new Data();
        $data['attr1'] = 'aaa';
        $data['attr3'] = 'ccc';
        $rst = Validate::assertInputValue($data, 'attr1');
        $this->assertSame($rst, 'aaa');

        // 不存在, 默认值
        $rst = Validate::assertInputValue($data, 'attr2', 'attr2value');
        $this->assertSame($rst, 'attr2value');

        // 不存在, 空
        $rst = Validate::assertInputValue($data, 'attr2.ept');
        $this->assertSame($rst, '');

        // 存在, 允许空
        $rst = Validate::assertInputValue($data, 'attr3.ept');
        $this->assertSame($rst, 'ccc');

        // $trustData = false 验证抛异常
        $this->checkException(
            function () use ($data) {
                Validate::assertInputValue($data, 'attr1', null, 'test_error', false);
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('test_error', 'str|max:2', 'aaa', 'attr1'),
            'test_error',
            ['key' => 'attr1']
        );

        // array验证, 抛异常
        $data = ['attr1' => 'aaa'];
        $this->checkException(
            function () use ($data) {
                Validate::assertInputValue($data, 'attr1', null, 'test_error');
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('test_error', 'str|max:2', 'aaa', 'attr1'),
            'test_error',
            ['key' => 'attr1']
        );
    }

    public function test_assertSysValue()
    {
        Validate::mergeRules([
            'attr1' => 'str|max:2',
            'attr2' => 'str|min:2',
            'attr3' => 'str|min:2',
        ]);

        // 信任Data不做验证, 'aaa'数据超过max:2
        $data = new Data();
        $data['attr1'] = 'aaa';
        $data['attr3'] = 'ccc';
        $rst = Validate::assertSysValue($data, 'attr1');
        $this->assertSame($rst, 'aaa');
        $rst = Validate::assertSysValue($data, 'attr2.ept', 'attr2value');
        $this->assertSame($rst, 'attr2value');
        $rst = Validate::assertSysValue($data, 'attr2.ept');
        $this->assertSame($rst, '');
        $rst = Validate::assertSysValue($data, 'attr3.ept');
        $this->assertSame($rst, 'ccc');

        // 不存在, 报错
        $this->checkException(
            function () use ($data) {
                Validate::assertSysValue($data, 'attr2');
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('', 'str|min:2', '', 'attr2'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );

        // $trustData = false 验证抛异常
        $this->checkException(
            function () use ($data) {
                Validate::assertSysValue($data, 'attr1', null, false);
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('', 'str|max:2', 'aaa', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );

        // array验证, 抛异常
        $data = ['attr1' => 'aaa'];
        $this->checkException(
            function () use ($data) {
                Validate::assertSysValue($data, 'attr1', null);
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('', 'str|max:2', 'aaa', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );
    }

    public function test_assertInputData()
    {
        Validate::mergeRules([
            'attr1' => 'str|max:2',
            'attr2' => 'str|min:2',
            'attr3' => 'str|min:2',
        ]);
        $data = new Data();
        $data['attr1'] = 'aaa';
        $data['attr3'] = 'ccc';

        $rst = Validate::assertInputData(
            $data,
            [
                'attr1',                 // 测试信任Data不做验证, 'aaa'数据超过max:2
                'attr2.ept' => 'value2', // 测试: ruleKey附带flag(ept), KV形式传默认值
                'attr3.ept',
            ]
        );
        $this->assertSame($rst['attr1'], 'aaa');
        $this->assertSame($rst['attr3'], 'ccc');

        // 不信任 Data对象 $trustData = false
        $msg = static::formatMsg('attr1', 'str_max', ['max' => 2], '');
        $this->checkException(
            function () use ($data) {
                Validate::assertInputData(
                    $data,
                    ['attr1'],
                    false,
                    false
                );
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            static::formatLog($msg, 'str|max:2', 'aaa', 'attr1'),
            $msg,
            null
        );

        // 全量检测 (多项错误)
        $msg1 = static::formatMsg('attr1', 'str_max', ['max' => 2], '');
        $msg2 = static::formatMsg('attr2', 'str_min', ['min' => 2], '');
        $this->checkException(
            function () use ($data) {
                Validate::assertInputData(
                    $data,
                    ['attr1', 'attr2'],
                    true,
                    false
                );
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            'Assert all errmsg: ' . substr(print_r(['attr1' => $msg1, 'attr2' => $msg2], true), 0, 100),
            Language::getMsg(TabbyConsts::LANG_PKG_ASSERT, TabbyConsts::LANG_KEY_ASSERT_ERROR_COUNT, ['count' => 2]),
            ['attr1' => $msg1, 'attr2' => $msg2]
        );
        // 全量检测 (一项错误)
        $this->checkException(
            function () use ($data) {
                Validate::assertInputData(
                    $data,
                    ['attr1', 'attr2.ept' => 'value2'],
                    true,
                    false
                );
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            'Assert all errmsg: ' . substr(print_r(['attr1' => $msg1], true), 0, 100),
            Language::getMsg(TabbyConsts::LANG_PKG_ASSERT, TabbyConsts::LANG_KEY_ASSERT_ERROR_COUNT, ['count' => 1]),
            ['attr1' => $msg1]
        );
    }

    public function test_assertSysData()
    {
        Validate::mergeRules([
            'attr1' => 'str|max:2',
            'attr2' => 'str|min:2',
        ]);
        $data = new Data();
        $data['attr1'] = 'aaa';
        $data['attr3'] = 'ccc';

        $rst = Validate::assertSysData(
            $data,
            [
                'attr1',                 // 测试信任Data不做验证, 'aaa'数据超过max:2
                'attr2.ept' => 'value2', // 测试: ruleKey附带flag(ept), KV形式传默认值
                'attr3.ept',
            ]
        );
        $this->assertSame($rst['attr1'], 'aaa');
        $this->assertSame($rst['attr2'], 'value2');
        $this->assertSame($rst['attr3'], 'ccc');

        // 不信任 Data对象 $trustData = false
        $this->checkException(
            function () use ($data) {
                Validate::assertSysData(
                    $data,
                    ['attr1'],
                    false
                );
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            static::formatLog('', 'str|max:2', 'aaa', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );

        // 第一项错误
        $this->checkException(
            function () use ($data) {
                Validate::assertSysData(
                    $data,
                    ['attr1', 'attr2'],
                    false
                );
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            static::formatLog('', 'str|max:2', 'aaa', 'attr1'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );

        // 第二项错误
        $data['attr1'] = 'a';
        $this->checkException(
            function () use ($data) {
                Validate::assertSysData(
                    $data,
                    ['attr1', 'attr2'],
                    false
                );
            },
            TabbyConsts::ERROR_ASSERT_TYPE,
            static::formatLog('', 'str|min:2', null, 'attr2'),
            \Tabby\Framework\Language::getMsg(TabbyConsts::LANG_PKE_ERROR, TabbyConsts::LANG_KEY_ERROR_DEFAULT),
            null
        );
    }

    public function test_checkValueByRule()
    {
        $this->assertSame(Validate::checkValueByRule('', 'str|min:3', 'ept', 'aaa'), 'aaa');
        $this->assertSame(Validate::checkValueByRule('', 'str|min:3', ''), false);
    }

    public function test_checkValueByKey()
    {
        Validate::mergeRules([
            'attr1' => 'str|min:3',
        ]);
        $this->assertSame(Validate::checkValueByKey('', 'attr1.ept', 'aaa'), 'aaa');
        $this->assertSame(Validate::checkValueByKey('', 'attr1'), false);
    }

    public function test_checkDataByKey()
    {
        Validate::mergeRules([
            'attr1' => 'str|max:2',
            'attr2' => 'str|min:2',
            'attr3' => 'str|min:3',
        ]);
        $data = new Data();
        $data['attr1'] = 'aaa';
        $data['attr3'] = 'ccc';

        $rst = Validate::checkDataByKey(
            $data,
            [
                'attr1',                 // 测试信任Data不做验证, 'aaa'数据超过max:2
                'attr2.ept' => 'value2', // 测试: ruleKey附带flag(ept), KV形式传默认值
                'attr3.ept',
            ]
        );
        $this->assertSame($rst['attr1'], 'aaa');
        $this->assertSame($rst['attr2'], 'value2');
        $this->assertSame($rst['attr3'], 'ccc');

        // 不信任 Data对象 $trustData = false
        $rst = Validate::checkDataByKey(
            $data,
            ['attr1', 'attr2'],
            false
        );
        $this->assertSame($rst, false);

        // 第二项错误
        $data['attr1'] = 'a';
        $rst = Validate::checkDataByKey(
            $data,
            ['attr1', 'attr2'],
            false
        );
        $this->assertSame($rst, false);
    }

    public function test_customMsg()
    {
        Validate::mergeRules([
            'attr1' => 'str|mobile',
        ]);

        // 信任Data不做验证, 'aaa'数据超过max:2
        $data = new Data();
        $data['attr1'] = '1';

        // 自定义错误信息
        Validate::mergeCustomMsg([
            'attr1.str_mobile' => 'mobile_custom_test',
        ]);
        $this->checkException(
            function () use ($data) {
                Validate::assertInputValue($data, 'attr1', null, '', false);
            },
            TabbyConsts::ERROR_INPUT_TYPE,
            //string $msg, string $rule, $value, $key = null
            static::formatLog('mobile_custom_test', 'str|mobile', '1', 'attr1'),
            'mobile_custom_test',
            null
        );
    }

    public function test_other()
    {
        Validate::mergeRules([
            'attr2' => 'other',
        ]);
        $this->assertSame(Validate::checkValueByKey('test_other', 'attr2', 'test_other_def'), 'test_other');
        $this->assertSame(Validate::checkValueByKey('test_other', 'attr2.ept', 'test_other_def'), 'test_other');
        $this->assertSame(Validate::checkValueByKey('', 'attr2.ept', 'test_other_def'), 'test_other_def');
        $this->assertSame(Validate::checkValueByKey(null, 'attr2.ept', 'test_other_def'), 'test_other_def');
    }

    public function test_patch()
    {
        // float 默认值 format格式化
        Validate::mergeRules([
            'attr1' => 'float:2|min:3',
        ]);
        $this->assertSame(Validate::checkValueByKey('', 'attr1.ept', '1.111'), 1.11);

        Validate::mergeRules([
            'attr1' => 'float|min:3',
        ]);
        $this->assertSame(Validate::checkValueByKey('', 'attr1.ept', '1.111'), 1.111);
    }

    private function checkException(\Closure $fn, $type, $logMsg, $msg = null, $data = null)
    {
        try {
            $fn();
        } catch (ErrorAbstract $e) {
            $this->assertSame($e->getType(), $type);
            $this->assertSame($e->getLogMessage(), $logMsg);
            if ($msg !== null) {
                $this->assertSame($e->getResponseMessage(), $msg);
            }
            if ($data !== null) {
                $this->assertSame($e->getResponseData(), $data);
            }
            return;
        } catch (\Throwable $e) {
            $this->assertSame('异常不对', get_class($e) . $e->getMessage());
            return;
        }
        $this->assertSame('没抛异常', '');
    }

    protected static function formatLog(string $msg, string $rule, $value, $key = ''): string
    {
        return static::call(Validate::class, 'formatLog', $msg, $rule, $value, $key);
    }

    protected static function formatMsg($key, $assertName, $msgAttr, $msg): string
    {
        return static::call(Validate::getRules(), 'formatMsg', $key, $assertName, $msgAttr, $msg);
    }
}
