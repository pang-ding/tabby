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

use Consts\TabbyConsts;

class RenderJsonp extends RenderJson
{
    protected static function send($data): void
    {
        header('Content-type: application/javascript; charset=UTF-8');

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Tabby\Tabby::$Log->critical('RenderJson JSON_ENCODE Error: ' . json_last_error_msg() . 'DATA => ' . serialize($data));
            $json = json_encode(static::formatException(new \Exception('E')));
        }

        $callback = isset($_GET[TabbyConsts::JSONP_CALLBACK_NAME]) ? trim($_GET[TabbyConsts::JSONP_CALLBACK_NAME]) : '';
        echo $callback . '(' . $json . ')';
    }
}
