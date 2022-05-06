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

use Consts\TabbyConsts;

/**
 * 客户端异常 (继承此异常会输出具体信息到客户端, 否则统一输出默认信息)
 * 一般用于: 由客户端状态、操作、逻辑错误导致的异常
 *
 * @param string      $responseMessage 输出给 Client 的异常信息
 * @param string      $type            异常类型, Client 根据这个参数判断异常如何处理 为空时默认 TabbyConsts::ERROR_CLIENT_TYPE
 * @param ?array      $responseData    输出给 Client 的异常参数, 与客户端约定
 * @param string      $logMessage      Log 内容 为空时默认 $responseMessage(第一个参数)
 * @param int         $code            异常代码 为0时默认 TabbyConsts::ERROR_CLIENT_CODE
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::NOTICE
 * @param ?\Throwable $previous
 */
class ErrorClient extends ErrorAbstract
{
    public function __construct(
        string $responseMessage,
        string $type = '',
        ?array $responseData = null,
        string $logMessage = '',
        int $code = 0,
        string $logLevel = \Psr\Log\LogLevel::NOTICE,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $logMessage ?: $responseMessage,
            $responseMessage,
            $type ?: TabbyConsts::ERROR_CLIENT_TYPE,
            $code ?: TabbyConsts::ERROR_CLIENT_CODE,
            $logLevel,
            $previous,
            $responseData
        );
    }
}
