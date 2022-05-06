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
use Tabby\Framework\Language;
use Tabby\Tabby;

/**
 * 抽象异常
 *
 * 为方便使用(语法提示) 统一以 ErrorXxxxxx 格式命名
 */
abstract class ErrorAbstract extends \Exception
{
    protected $_responseMessage = '';
    protected $_responseData    = null;
    protected $_type            = '';
    protected $_logLevel        = \Psr\Log\LogLevel::ERROR;

    public function __construct(
        string $logMessage,
        string $responseMessage,
        string $type,
        int $code,
        string $logLevel,
        ?\Throwable $previous = null,
        ?array $responseData = null
    ) {
        parent::__construct($logMessage, $code, $previous);
        $this->_responseMessage = $responseMessage;
        $this->_responseData    = $responseData;
        $this->_logLevel        = $logLevel;
        $this->_type            = $type;

        Tabby::$Log->$logLevel($logMessage);
    }

    public function getType()
    {
        return $this->_type ?: TabbyConsts::ERROR_SYS_TYPE;
    }

    public function getLogMessage()
    {
        $message = $this->getMessage();

        return $message ?: TabbyConsts::ERROR_DEFAULT_MSG;
    }

    public function getResponseMessage()
    {
        if (empty($this->_responseMessage)) {
            return \Tabby\Framework\Language::getMsg(
                TabbyConsts::LANG_PKE_ERROR,
                TabbyConsts::LANG_KEY_ERROR_DEFAULT,
                null,
                TabbyConsts::ERROR_DEFAULT_MSG
            );
        }

        return Language::change($this->_responseMessage);
    }

    public function getResponseData()
    {
        return $this->_responseData;
    }
}
