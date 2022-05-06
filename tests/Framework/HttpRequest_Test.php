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

use Consts\TabbyConsts;
use Tabby\Test\TestCase;
use Tabby\Framework\Language;
use Tabby\Validator\Validate;
use Tabby\Framework\Request\HttpRequest;

class HttpRequest_Test extends TestCase
{
    /**
     * @var HttpRequest
     */
    public static $_req;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        Validate::mergeRules(
            [
                'string'    => 'str|between:2,20',
                'none'      => 'str|between:2,20',
            ]
        );
        static::$_req = HttpRequest::getIns();
    }

    public function test_request()
    {
        $_REQUEST['string'] = 'test_string';
        $this->assertSame(static::$_req->request('string'), 'test_string');
        $this->assertSame(static::$_req->request('none.ept', 'default'), 'default');

        try {
            static::$_req->request('none', null, 'test_msg');
        } catch (\Tabby\Error\ErrorInput $e) {
            $this->assertSame($e->getResponseMessage(), 'test_msg');
        }
    }

    public function test_get()
    {
        $_GET['string'] = 'test_string';
        $this->assertSame(static::$_req->get('string'), 'test_string');
        $this->assertSame(static::$_req->get('none.ept', 'default'), 'default');

        try {
            static::$_req->get('none', null, 'test_msg');
        } catch (\Tabby\Error\ErrorInput $e) {
            $this->assertSame($e->getResponseMessage(), 'test_msg');
        }
    }

    public function test_post()
    {
        $_POST['string'] = 'test_string';
        $this->assertSame(static::$_req->post('string'), 'test_string');
        $this->assertSame(static::$_req->post('none.ept', 'default'), 'default');

        try {
            static::$_req->post('none', null, 'test_msg');
        } catch (\Tabby\Error\ErrorInput $e) {
            $this->assertSame($e->getResponseMessage(), 'test_msg');
        }
    }

    public function test_data()
    {
        $_REQUEST['string'] = 'test_string';
        $_REQUEST['none']   = '';

        try {
            static::$_req->data(['string', 'none']);
        } catch (\Tabby\Error\ErrorInput $e) {
            $this->assertSame($e->getResponseMessage(), 'none ' . Language::getMsg('assert', 'str_between', ['min'=>2, 'max'=>20]));
            $this->assertSame($e->getResponseData(), ['key'=>'none']);
        }

        $_REQUEST['string'] = '';

        try {
            static::$_req->data(['string', 'none']);
        } catch (\Tabby\Error\ErrorInput $e) {
            $this->assertSame($e->getResponseMessage(), 'string ' . Language::getMsg('assert', 'str_between', ['min'=>2, 'max'=>20]));
            $this->assertSame($e->getResponseData(), ['key'=>'string']);
        }

        try {
            static::$_req->data(['string', 'none'], true);
        } catch (\Tabby\Error\ErrorInput $e) {
            $this->assertSame($e->getResponseMessage(), Language::getMsg('assert', 'error_count', ['count'=>2]));
            $this->assertSame($e->getResponseData(), ['string'=>'string ' . Language::getMsg('assert', 'str_between', ['min'=>2, 'max'=>20]), 'none'=>'none ' . Language::getMsg('assert', 'str_between', ['min'=>2, 'max'=>20])]);
        }

        $this->assertSame(static::$_req->data(['string'=>'str_def', 'none'=>'none_def'])->toArray(), ['string'=>'str_def', 'none'=>'none_def']);
    }

    public function test_cookie()
    {
        $_COOKIE['string'] = ' test_string ';
        $this->assertSame(static::$_req->cookie('string'), 'test_string');

        $this->assertSame(static::$_req->cookie('string', 'str|min:30'), '');
        $this->assertSame(static::$_req->cookie('string', 'str|min:30', 'test_def'), 'test_def');
        $this->assertSame(static::$_req->cookie('string', 'str|min:1', '', 'notrim'), ' test_string ');
    }

    public function test_getPages()
    {
        Validate::mergeRules(
            [
                TabbyConsts::PAGE_NUM_KEY  => 'int|between:1,10000',
                TabbyConsts::PAGE_SIZE_KEY => 'int|between:1,1000',
            ]
        );
        $this->assertSame(static::$_req->getPages(), [TabbyConsts::PAGE_SIZE_DEFAULT, 1]);
        $this->assertSame(static::$_req->getPages(10), [10, 1]);

        $_REQUEST[TabbyConsts::PAGE_SIZE_KEY]= '15';
        $_REQUEST[TabbyConsts::PAGE_NUM_KEY] = '2';
        $this->assertSame(static::$_req->getPages(), [TabbyConsts::PAGE_SIZE_DEFAULT, 2]);
        $this->assertSame(static::$_req->getPages(TabbyConsts::PAGE_SIZE_DEFAULT, TabbyConsts::PAGE_NUM_KEY, TabbyConsts::PAGE_SIZE_KEY), [15, 2]);
    }

    public function test_exists()
    {
        $_REQUEST['test_exists'] = ' test_exists ';
        $this->assertSame(static::$_req->exists('test_exists'), true);
        $_REQUEST['test_exists'] = '';
        $this->assertSame(static::$_req->exists('test_exists'), true);
        $_REQUEST['test_exists'] = null;
        $this->assertSame(static::$_req->exists('test_exists'), false);
        unset($_REQUEST['test_exists']);
        $this->assertSame(static::$_req->exists('test_exists'), false);
    }

    public function test_array_get()
    {
        $_REQUEST['string'] = 'test_string';
        $this->assertSame(static::$_req['string'], 'test_string');
    }

    public function test_object_get()
    {
        $_REQUEST['string'] = 'test_string';
        $this->assertSame(static::$_req->string, 'test_string');
    }
}
