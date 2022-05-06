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
use Tabby\Utils\Happy;

class Happy_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
    }

    public function test_sleepSort()
    {
        $this->assertTrue(Happy::sleepSort([3, 2, 1, 7, 6, 5, 4]));
    }

    public function test_todayOfNextYear()
    {
        $this->assertTrue(Happy::todayOfNextYear());
    }
}
