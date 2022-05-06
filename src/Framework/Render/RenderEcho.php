<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework\Render;

class RenderEcho extends RenderAbstract
{
    const CONTENT_KEY = '__echo_content__';

    public static function display(\Tabby\Framework\Response $response)
    {
        if (isset($response[self::CONTENT_KEY])) {
            echo $response[self::CONTENT_KEY];
        }
    }

    public static function exception(\Throwable $exception): void
    {
        $errData = self::formatErrorData($exception);
        echo "{$errData['err_code']} {$errData['err_msg']}";
    }
}
