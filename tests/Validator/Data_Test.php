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
use Tabby\Validator\Data;
use Tabby\Validator\Validator;

class Data_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
    }

    public function test_merge()
    {
        $d = new Data(['a' => 'a', 'b' => 'b']);
        $d = $d->merge(['b' => 'bb', 'c' => 'cc']);
        $d = $d->merge(new Data(['d' => 'dd', 'e' => 'ee']));
        $d = $d->merge(new Data([]));
        $d = $d->merge([]);

        $this->assertSame($d['a'], 'a');
        $this->assertSame($d['b'], 'bb');
        $this->assertSame($d['c'], 'cc');
        $this->assertSame($d['d'], 'dd');
        $this->assertSame($d['e'], 'ee');

        $this->assertException(
            \Tabby\Error\ErrorSys::class,
            function () use ($d) {
                $d->merge(1);
            }
        );
        $this->assertSame($d->merge(1, false), $d);
    }

    public function test_unsetNull()
    {
        $d = new Data(['a' => 0, 'b' => null, 'c' => '']);
        $d = $d->unsetNull();
        $this->assertSame((array) $d, ['a' => 0, 'c' => '']);
    }

    public function test_unsetEmpty()
    {
        $d = new Data(['a' => 0, 'b' => null, 'c' => '', 'd' => '0', 'e' => false, 'f' => null, 'e' => 'var']);
        $d = $d->unsetEmpty();
        $this->assertSame((array) $d, ['e' => 'var']);
    }

    public function test_replaceKey()
    {
        $d = new Data(['a' => 'a', 'b' => 'b']);
        $d = $d->replaceKey(['a' => 'aa']);
        $this->assertSame((array) $d, ['b' => 'b', 'aa' => 'a']);
        $d = new Data(['a' => 'a', 'b' => 'b']);
        $d = $d->replaceKey(['a' => 'aa'], true);
        $this->assertSame((array) $d, ['aa' => 'a', 'b' => 'b']);
        $d = new Data(['a' => 'a', 'b' => 'b']);
        $d = $d->replaceKey(['a' => 'aa', 'b' => 'bb']);
        $this->assertSame((array) $d, ['aa' => 'a', 'bb' => 'b']);
    }
}
