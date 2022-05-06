<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Framework;

use Tabby\Framework\Render\RenderEcho;
use Tabby\Framework\Response;
use Tabby\Test\Context;
use Tabby\Test\TestCase;

class Cycle_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        \T::$RSP->setDefaultRender(Response::RENDER_ECHO);
    }

    public function test_router()
    {
        Context::$value1 = '';
        \T::$YAF_REQ->setRequestUri('/index/test_test');
        $_REQUEST['test'] = 'request_test';
        ob_start();
        Context::$app->run();
        $rst = ob_get_contents();
        ob_end_clean();
        // 经过一次跳转 IndexController::testTestAction -> IndexController::test1Action
        // IndexController::testTestAction 输出 $_REQUEST['test'](过Validator)
        // IndexController::test1Action 输出 inited IndexController::init() 赋值
        $this->assertSame(Context::$value1, 'IndexController::testTestAction/request_test/IndexController::test1Action/inited/');
        // response只输出最后赋值的echo信息
        $this->assertSame($rst, 'IndexController::test1Action');
    }

    // public function test_error()
    // {
    //     Context::$value1 = '';
    //     \T::$YAF_REQ->setActionName('error');
    //     Context::$value2 = 'err_msg';
    //     Context::$app->run();
    //     $rst = ob_get_contents();
    //     ob_clean();
    //     $this->assertSame($rst, '500 Server Error!');
    // }
}
