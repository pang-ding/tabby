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
 * 输入参数异常
 *
 * 一般用于: 由客户端输入参数导致的异常
 *
 * @param string      $responseMessage 输出给 Client 的异常信息
 * @param ?array      $responseData    输出给 Client 的异常参数, 与客户端约定
 * @param string      $logMessage      Log 内容 为空时默认等于 $responseMessage(第一个参数)
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::INFO
 * @param ?\Throwable $previous
 */
class ErrorInput extends ErrorClient
{
    public function __construct(
        string $responseMessage,
        ?array $responseData = null,
        string $logMessage = '',
        string $logLevel = \Psr\Log\LogLevel::INFO,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $responseMessage,
            TabbyConsts::ERROR_INPUT_TYPE,
            $responseData,
            $logMessage,
            TabbyConsts::ERROR_INPUT_CODE,
            $logLevel,
            $previous,
        );
    }
}
