<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Consts\TabbyConsts;
use Tabby\Test\Context;

class ErrorController extends \Yaf\Controller_Abstract
{
    private static $_Counter = 0;
    private static $_EndlessLimit = 1;

    public function errorAction(\Throwable $exception)
    {
        // 曾经吃过亏
        self::$_Counter++;
        if (self::$_Counter > self::$_EndlessLimit) {
            echo TabbyConsts::ERROR_DEFAULT_MSG;
            exit;
        }

        Context::$value1 .= __METHOD__ . '/';
        \T::$RSP->setException($exception);
    }
}
