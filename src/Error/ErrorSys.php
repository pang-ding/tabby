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
 * 系统异常
 *
 * 一般用于: 系统编码或逻辑级错误引发的异常
 *
 * @param string      $logMessage      Log 内容
 * @param string      $responseMessage 输出给 Client 的异常信息 为空时默认 TabbyConsts::ERROR_DEFAULT_MSG
 * @param string      $type            异常类型, Client 根据这个参数判断异常如何处理 为空时默认 TabbyConsts::ERROR_SYS_TYPE
 * @param int         $code            错误代码 记入LOG并输出 为0时默认 TabbyConsts::ERROR_SYS_TYPE
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::CRITICAL
 * @param ?\Throwable $previous
 */
class ErrorSys extends ErrorAbstract
{
    protected $_responseMessage = '';

    public function __construct(
        string $logMessage,
        string $responseMessage = '',
        string $type = '',
        int $code = 0,
        string $logLevel = \Psr\Log\LogLevel::CRITICAL,
        ?\Throwable $previous = null
    ) {
        try {
            parent::__construct(
                $logMessage,
                $responseMessage,
                $type ?: TabbyConsts::ERROR_SYS_TYPE,
                $code ?: TabbyConsts::ERROR_SYS_CODE,
                $logLevel,
                $previous
            );
        } catch (\Throwable $e) {
        }
    }
}
