<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Utils;

use Tabby\Test\TestCase;
use Tabby\Utils\ArrayUtils;
use Tabby\Utils\StrUtils;
use Tabby\Utils\Timer;
use Tabby\Utils\Validate;

class All_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
    }

    public function test_Timer()
    {
        $t = new Timer();
        usleep(100000);
        $this->assertTrue($t->getSecond() > 0.1);
        $this->assertTrue($t->getSecond() < 0.2);
        $this->assertSame($t->getSecond(1), 0.1);
    }

    public function test_Str()
    {
        $this->assertSame(StrUtils::camelize('abc_def_ghi'), 'AbcDefGhi');
        $this->assertSame(StrUtils::camelize('abc.def.ghi', '.'), 'AbcDefGhi');
        $this->assertSame(StrUtils::uncamelize('AbcDefGhi'), 'abc_def_ghi');
        $this->assertSame(StrUtils::uncamelize('AbcDefGhi', '.'), 'abc.def.ghi');
    }

    public function test_Str_templateReplace()
    {
        $this->assertSame(
            StrUtils::templateReplace(
                '测试模板{{a}}{a}<b>',
                null
            ),
            '测试模板{{a}}{a}<b>'
        );
        $this->assertSame(
            StrUtils::templateReplace(
                '测试模板{{a}}{a}<b>',
                []
            ),
            '测试模板{{a}}{a}<b>'
        );
        $this->assertSame(
            StrUtils::templateReplace(
                '测试模板{{a}}{a}<b>',
                ['a' => 'AA']
            ),
            '测试模板AA{a}<b>'
        );
        $this->assertSame(
            StrUtils::templateReplace(
                '测试模板{{a}}{a}<b>',
                ['a' => 'AA'],
                '{',
                '}'
            ),
            '测试模板{AA}AA<b>'
        );
        $this->assertSame(
            StrUtils::templateReplace(
                '测试模板{{a}}{a}<bb>',
                ['bb' => 'BB'],
                '<',
                '>'
            ),
            '测试模板{{a}}{a}BB'
        );
    }

    public function test_ArrayUtils_replaceKey()
    {
        $data = ['aa' => 'a', 'bb' => 'b', 'aaaa' => 'aa'];
        $this->assertSame(ArrayUtils::replaceKey($data, ['aa' => 'cc'], true), ['cc' => 'a', 'bb' => 'b', 'aaaa' => 'aa']);
        $this->assertSame(ArrayUtils::replaceKey($data, ['aa' => 'cc', 'bb' => 'dd'], true), ['cc' => 'a', 'dd' => 'b', 'aaaa' => 'aa']);
        $data = ['aa' => 'a', 'bb' => 'b', 'aaaa' => 'aa'];
        $this->assertSame(ArrayUtils::replaceKey($data, ['aa' => 'cc']), ['bb' => 'b', 'aaaa' => 'aa', 'cc' => 'a']);
        $this->assertSame(ArrayUtils::replaceKey($data, ['aa' => 'cc', 'bb' => 'dd']), ['aaaa' => 'aa', 'cc' => 'a', 'dd' => 'b']);
        $this->assertSame(ArrayUtils::replaceKey(null, ['a' => 'aa']), null);
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () use ($data) {
                ArrayUtils::replaceKey($data, ['cc' => 'aa']);
            }
        );
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                ArrayUtils::replaceKey([], [0 => 'aa']);
            }
        );
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                ArrayUtils::replaceKey([], ['10' => 'aa']);
            }
        );
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                ArrayUtils::replaceKey(['a' => '10', '20' => '20'], ['a' => '20']);
            }
        );
        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () {
                ArrayUtils::replaceKey(1, ['a' => '20']);
            }
        );
    }

    public function test_Validate()
    {
        $this->assertSame(Validate::isEmail('123@asd.com'), true);
        $this->assertSame(Validate::isEmail(''), false);
        $this->assertSame(Validate::isEmail(1), false);
        $this->assertSame(Validate::isEmail('aaaaa.com'), false);
        $this->assertSame(Validate::isEmail('asd"asd@aaaaa.com'), false);
        $this->assertSame(Validate::isEmail('asd\'asd@aaaaa.com'), false);
        $this->assertSame(Validate::isEmail('asd&asd@aaaaa.com'), false);
        $this->assertSame(Validate::isEmail('asd<asd@aaaaa.com'), false);
        $this->assertSame(Validate::isMobile('13000000000'), true);
        $this->assertSame(Validate::isMobile(13000000000), true);
        $this->assertSame(Validate::isMobile('1300000000'), false);
        $this->assertSame(Validate::isMobile('130000000001'), false);
        $this->assertSame(Validate::isMobile('130a0000000'), false);
        $this->assertSame(Validate::isMobile(''), false);
    }
}
