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

use Tabby\Error\ErrorClient;

class RenderJson extends RenderAbstract
{
    /**
     * 正常数据格式化方法
     *
     * @var \Closure
     */
    protected static $_formatData = null;

    /**
     * 异常数据格式化方法
     *
     * @var \Closure
     */
    protected static $_formatException = null;

    /**
     * 设置正常数据格式化方法
     *
     * @param \Closure $formatData
     *
     */
    public static function setFormatData(\Closure $formatData): void
    {
        static::$_formatData = $formatData;
    }

    /**
     * 设置异常数据格式化方法
     *
     * @param \Closure $formatException
     *
     */
    public static function setFormatException(\Closure $formatException): void
    {
        static::$_formatException = $formatException;
    }

    public static function display(\Tabby\Framework\Response $response): void
    {
        static::send(static::formatData($response));
    }

    public static function exception(\Throwable $exception): void
    {
        static::send(static::formatException($exception));
    }

    public static function redirect($url)
    {
        header("Location: {$url}");

        static::exception(new ErrorClient('', 'redirect', ['url' => $url]));
    }

    protected static function send($data): void
    {
        header('Content-type: application/json; charset=UTF-8');

        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Tabby\Tabby::$Log->critical('RenderJson JSON_ENCODE Error: ' . json_last_error_msg() . 'DATA => ' . serialize($data));
            $json = json_encode(static::formatException(new \Exception('E')));
        }

        echo $json;
    }

    protected static function formatException(\Throwable $exception): array
    {
        $errData = self::formatErrorData($exception);
        if (static::$_formatException !== null) {
            return (static::$_formatException)($errData, $exception);
        }

        return $errData;
    }

    protected static function formatData(\Tabby\Framework\Response $response): array
    {
        if (static::$_formatData !== null) {
            return (static::$_formatData)($response);
        }

        $data['err_code'] = 0;
        $data             = array_merge($data, ['data' => $response->getAll()]);

        return $data;
    }
}
