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
 * 内部数据异常 (WARNING级别)
 *
 * 一般用于: 前期业务逻辑对输入数据验证不严引发的异常
 * 与 ErrorData 区别: 默认输出 WARNING 级别日志
 * Validator 使用该异常
 *
 * @param string $logMessage      记入LOG
 * @param string $responseMessage 输出信息
 * @param string $logLevel        日志级别 默认 \Psr\Log\LogLevel::WARNING
 */
class ErrorAssert extends ErrorAbstract
{
    protected $_responseMessage = '';

    public function __construct(
        string $logMessage,
        string $responseMessage = '',
        string $logLevel = \Psr\Log\LogLevel::WARNING
    ) {
        parent::__construct(
            $logMessage,
            $responseMessage,
            TabbyConsts::ERROR_ASSERT_TYPE,
            TabbyConsts::ERROR_ASSERT_CODE,
            $logLevel
        );
    }
}
