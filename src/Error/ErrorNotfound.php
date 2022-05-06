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
 * 404
 *
 * @param string      $uri
 * @param string      $logLevel 日志级别 默认 \Psr\Log\LogLevel::INFO
 * @param ?\Throwable $previous
 */
class ErrorNotfound extends ErrorClient
{
    public function __construct(
        string $uri,
        string $logLevel = \Psr\Log\LogLevel::INFO,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            \Tabby\Framework\Language::getMsg('error', 'notfound'),
            TabbyConsts::ERROR_NOTFOUND_TYPE,
            [],
            'Notfound: ' . $uri,
            TabbyConsts::ERROR_NOTFOUND_CODE,
            $logLevel,
            $previous,
        );
    }
}
