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

use Tabby\Test\TestCase;
use Tabby\Framework\Response;

class Response_Test extends TestCase
{
    /**
     * @var Response
     */
    public static $_rsp;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        static::$_rsp = Response::getIns();
    }

    public function test_set()
    {
        static::$_rsp->clear();
        static::$_rsp['test'] = 'test';

        $this->assertSame(static::$_rsp['test'], 'test');
        $this->assertSame(isset(static::$_rsp['test']), true);
        $this->assertSame(static::$_rsp->getAll(), ['test'=>'test']);
        unset(static::$_rsp['test']);
        $this->assertSame(isset(static::$_rsp['test']), false);
    }

    public function test_merge()
    {
        static::$_rsp->clear();
        static::$_rsp['test'] = 'test';
        static::$_rsp->merge(['test2'=>'test2']);
        $this->assertSame(static::$_rsp->getAll(), ['test'=>'test', 'test2'=>'test2']);
    }

    public function test_reset()
    {
        static::$_rsp->reset(['test2'=>'test2']);
        $this->assertSame(static::$_rsp->getAll(), ['test2'=>'test2']);
    }

    public function test_Exception()
    {
        $e = new \Exception('testException');
        static::$_rsp->setException($e);

        $this->assertSame(static::$_rsp->getException()->getMessage(), 'Exception: testException');
        static::$_rsp->clearException();
        $this->assertSame(static::$_rsp->getException(), null);
    }
}
