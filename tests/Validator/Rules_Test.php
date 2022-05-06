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
use Tabby\Test\TestCase;
use Tabby\Validator\Rules;
use Tabby\Validator\Validator;

class Rules_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_rules()
    {
        $r = new Rules(['a' => 'a', 'b' => 'b']);
        $r['c'] = 'c';

        $this->assertSame($r['a'], 'a');
        $this->assertSame($r['b'], 'b');
        $this->assertSame($r['c'], 'c');
        $this->assertSame(isset($r['a']), true);
        unset($r['a']);
        $this->assertSame(isset($r['a']), false);
        $r->merge(['b' => 'bb', 'c' => 'cc']);
        $this->assertSame($r['b'], 'bb');
        $this->assertSame($r['c'], 'cc');
    }

    public function test_stripFlagByRuleStr()
    {
        // [
        //     'ept' => Validator::FLAG_EMPTY,
        //     'arr' => Validator::FLAG_ARRAY,
        //     'notrim' => Validator::FLAG_OFF_TRIM,
        //     'noxss' => Validator::FLAG_OFF_XSS,
        //     'nofmt' => Validator::FLAG_OFF_FORMAT,
        // ];
        $rule = 'a.ept.arr.notrim.noxss.nofmt';
        $flag = Rules::stripFlagByRuleStr($rule);
        $this->assertSame($rule, 'a');
        $this->assertSame(
            $flag,
            Validator::FLAG_EMPTY | Validator::FLAG_ARRAY | Validator::FLAG_OFF_TRIM | Validator::FLAG_OFF_XSS | Validator::FLAG_OFF_FORMAT
        );
        $rule = ' a.ept.arr.notrim.nofmt ';
        $flag = Rules::stripFlagByRuleStr($rule);
        $this->assertSame($rule, 'a');
        $this->assertSame(
            $flag,
            Validator::FLAG_EMPTY | Validator::FLAG_ARRAY | Validator::FLAG_OFF_TRIM | Validator::FLAG_OFF_FORMAT
        );
        $rule = 'a.ept';
        $flag = Rules::stripFlagByRuleStr($rule);
        $this->assertSame($rule, 'a');
        $this->assertSame($flag, Validator::FLAG_EMPTY);
        $rule = 'a';
        $flag = Rules::stripFlagByRuleStr($rule);
        $this->assertSame($rule, 'a');
        $this->assertSame($flag, 0);
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                $r = 'a.ept.b';
                Rules::stripFlagByRuleStr($r);
            }
        );
    }

    public function test_normalizeFlag()
    {
        $this->assertSame(Rules::normalizeFlag(null), TabbyConsts::VALIDATOR_FLAG_DEFAULT);
        $this->assertSame(
            Rules::normalizeFlag('ept.arr.notrim.nofmt'),
            Validator::FLAG_EMPTY | Validator::FLAG_ARRAY | Validator::FLAG_OFF_TRIM | Validator::FLAG_OFF_FORMAT
        );
        $this->assertSame(
            Rules::normalizeFlag(Validator::FLAG_EMPTY | Validator::FLAG_ARRAY | Validator::FLAG_OFF_TRIM | Validator::FLAG_OFF_FORMAT),
            Validator::FLAG_EMPTY | Validator::FLAG_ARRAY | Validator::FLAG_OFF_TRIM | Validator::FLAG_OFF_FORMAT
        );
    }
}
