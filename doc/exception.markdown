# 异常

#### ErrorClient

一般用于: 由客户端状态、操作、逻辑错误导致的异常

继承此异常会输出具体信息到客户端, 否则统一输出默认信息

```php
/**
 * @param string      $responseMessage 输出给 Client 的异常信息
 * @param string      $type            异常类型, Client 根据这个参数判断异常如何处理 为空时默认 TabbyConsts::ERROR_CLIENT_TYPE
 * @param ?array      $responseData    输出给 Client 的异常参数, 与客户端约定
 * @param string      $logMessage      Log 内容 为空时默认 $responseMessage(第一个参数)
 * @param int         $code            异常代码 为0时默认 TabbyConsts::ERROR_CLIENT_CODE
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::NOTICE
 * @param ?\Throwable $previous
 */
ErrorClient(
    string $responseMessage,
    string $type = '',
    ?array $responseData = null,
    string $logMessage = '',
    int $code = 0,
    string $logLevel = \Psr\Log\LogLevel::NOTICE,
    ?\Throwable $previous = null
)
```

#### ErrorInput

一般用于: 由客户端输入参数导致的异常

```php
/**
 * @param string      $responseMessage 输出给 Client 的异常信息
 * @param ?array      $responseData    输出给 Client 的异常参数, 与客户端约定
 * @param string      $logMessage      Log 内容 为空时默认等于 $responseMessage(第一个参数)
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::INFO
 * @param ?\Throwable $previous
 */
ErrorInput(
    string $responseMessage,
    ?array $responseData = null,
    string $logMessage = '',
    string $logLevel = \Psr\Log\LogLevel::INFO,
    ?\Throwable $previous = null
)
```

#### ErrorSys

一般用于: 系统内部数据错误引发的异常

```php
/**
 * @param string      $logMessage      Log 内容
 * @param string      $responseMessage 输出给 Client 的异常信息 为空时默认 TabbyConsts::ERROR_DEFAULT_MSG
 * @param string      $type            异常类型, Client 根据这个参数判断异常如何处理 为空时默认 TabbyConsts::ERROR_SYS_TYPE
 * @param int         $code            错误代码 记入LOG并输出 为0时默认 TabbyConsts::ERROR_SYS_TYPE
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::CRITICAL
 * @param ?\Throwable $previous
 */
ErrorSys(
    string $logMessage,
    string $responseMessage = '',
    string $type = '',
    int $code = 0,
    string $logLevel = \Psr\Log\LogLevel::CRITICAL,
    ?\Throwable $previous = null
)
```

#### ErrorData

一般用于: 系统内部数据错误引发的异常

```php
/**
 * @param string      $logMessage      Log 内容
 * @param string      $responseMessage 输出给 Client 的异常信息 为空时默认 TabbyConsts::ERROR_DEFAULT_MSG
 * @param string      $type            异常类型, Client 根据这个参数判断异常如何处理 为空时默认 TabbyConsts::ERROR_DATA_TYPE
 * @param int         $code            错误代码 为0时默认 TabbyConsts::ERROR_DATA_CODE
 * @param string      $logLevel        日志级别 默认 \Psr\Log\LogLevel::ERROR
 * @param ?\Throwable $previous
 */
ErrorData(
    string $logMessage,
    string $responseMessage = '',
    string $type = '',
    int $code = 0,
    string $logLevel = \Psr\Log\LogLevel::ERROR,
    ?\Throwable $previous = null
)
```
