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
use ErrorClient;
use Tabby\Error\ErrorAbstract;
use Tabby\Framework\Response;

abstract class RenderAbstract
{
    abstract public static function display(Response $response);

    abstract public static function exception(\Throwable $exception);

    // 不支持 301 不写HTTP状态码
    // @todo: 各渲染器应当自行实现应对客户端无法自动跳转的情况
    public static function redirect($url)
    {
        header("Location: {$url}");
    }

    public static function response(Response $response): void
    {
        if (ob_get_length() > 0) {
            \Tabby\Tabby::$Log->error('Ob_save: ' . ob_get_clean()) ;
        }
        $exception = $response->getException();
        if ($exception !== null && $exception instanceof \Throwable) {
            static::exception($exception);
        } else {
            $redirect = $response->getRedirect();
            if ($redirect !== '') {
                static::redirect($redirect);
                exit;
            } else {
                if (\Tabby\Tabby::$YAF_REQ->isDispatched()) {
                    static::display($response);
                }
            }
        }
    }

    public static function formatErrorData(\Throwable $exception): array
    {
        if ($exception instanceof ErrorAbstract) {
            if ($exception instanceof ErrorClient) {
                return [
                    'err_code' => $exception->getCode(),
                    'err_type' => $exception->getType(),
                    'err_msg'  => $exception->getResponseMessage(),
                    'err_data' => $exception->getResponseData()
                ];
            } else {
                return [
                    'err_code' => $exception->getCode(),
                    'err_type' => $exception->getType(),
                    'err_msg'  => $exception->getResponseMessage()
                ];
            }
        } else {
            return [
                'err_code' => TabbyConsts::ERROR_SYS_CODE,
                'err_type' => TabbyConsts::ERROR_SYS_TYPE,
                'err_msg'  => TabbyConsts::ERROR_DEFAULT_MSG,
            ];
        }
    }
}
