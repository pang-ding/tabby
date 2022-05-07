<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ErrorController extends \Yaf\Controller_Abstract
{
    public function errorAction(\Throwable $exception)
    {
        \T::$Log->error($exception->getMessage());
        if (\T::$isDebug) {
            print_r("\e[0;31mErroMsg: " . $exception->getMessage() . "\n\e[0m");
            print_r("\e[0;33m" . $exception->getTraceAsString() . "\n\e[0m");
        }
    }
}
