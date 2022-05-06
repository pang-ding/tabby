<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Utils;

class Timer
{
    private $_start;

    public function __construct()
    {
        $this->_start = microtime(true);
    }

    public function getSecond(int $precision = 4): float
    {
        return round(microtime(true) - $this->_start, $precision);
    }
}
