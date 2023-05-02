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
    private static $_Counter      = 0;
    private static $_EndlessLimit = 1;

    public function errorAction(\Throwable $exception)
    {
        self::$_Counter++;
        if (self::$_Counter > self::$_EndlessLimit) {
            echo \Consts\TabbyConsts::ERROR_DEFAULT_MSG;
            exit;
        }

        if (\T::$isDebug) {
            \T::$Log->debug($exception->getFile() . ' (' . $exception->getLine() . ')');
            \T::$Log->debug('-----------Trace-----------' . substr(var2str($exception->getTraceAsString()), 3, -5));
            \T::$Log->debug('------------End------------');
        }
        \T::$RSP->setException($exception);
    }
}
