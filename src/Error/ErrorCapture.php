<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Error;

use Tabby\Error\ErrorNotfound;

class ErrorCapture
{
    public static function capture(?\Throwable $exception)
    {
        if ($exception === null) {
            return null;
        }
        if ($exception instanceof ErrorAbstract) {
            return $exception;
        }
        $eName = get_class($exception);
        switch ($eName) {
            case 'Yaf\Exception\LoadFailed\Controller':
            case 'Yaf\Exception\LoadFailed\Action':
                    return new ErrorNotfound(\Tabby\Tabby::$YAF_REQ->getRequestUri());
        }

        return new ErrorSys($eName . ': ' . $exception->getMessage(), '', '', 0, \Psr\Log\LogLevel::ERROR, $exception);
    }
}
