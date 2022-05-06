<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework\Request;

use Consts\TabbyConsts;
use Tabby\Validator\Data;
use Tabby\Validator\Validate;
use Tabby\Validator\Validate as Validator;

class HttpRequest extends AbstractRequest implements \ArrayAccess
{
    /**
     * 从 $_REQUEST 获取外部参数
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     * @param string $msg     自定义异常
     *
     * @return mixed
     */
    public function request(string $key, $default = null, string $msg = '')
    {
        return Validator::assertInputValue($_REQUEST, $key, $default, $msg);
    }

    /**
     * 从 $_GET 获取外部参数
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     * @param string $msg     自定义异常
     *
     * @return mixed
     */
    public function get(string $key, $default = null, string $msg = '')
    {
        return Validator::assertInputValue($_GET, $key, $default, $msg);
    }

    /**
     * 从 $_POST 获取外部参数
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     * @param string $msg     自定义异常
     *
     * @return mixed
     */
    public function post(string $key, $default = null, string $msg = '')
    {
        return Validator::assertInputValue($_POST, $key, $default, $msg);
    }

    /**
     * 从 $_REQUEST 批量获取外部参数
     *
     * @param array $ruleKeys  变量名数组
     * @param bool  $assertAll 验证全部参数并返回全部错误信息, 默认:false, 遇到一个参数验证失败直接返回错误
     *
     * @return Data
     */
    public function data(array $ruleKeys, bool $assertAll = false): Data
    {
        return Validator::assertInputData($_REQUEST, $ruleKeys, $assertAll);
    }

    /**
     * 检查 $_REQUEST 中是否存在 $key (isset, 不是array_key_exists)
     *
     * @param string $key 参数名
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return isset($_REQUEST[$key]);
    }

    /**
     * 从 $_COOKIE 获取参数
     *
     * @param string $key     参数名
     * @param string $rule    默认: str|between:1,1000
     * @param string $default 默认值
     * @param mixed  $flag    Validate Flag (ept/arr/noxss ...)
     *
     * @return mixed
     */
    public function cookie(string $key, string $rule = 'str|between:1,1000', string $default = '', $flag = TabbyConsts::VALIDATOR_FLAG_DEFAULT)
    {
        $val = Validate::checkValueByRule($_COOKIE[$key] ?? '', $rule, $flag, $default);

        return $val ?: $default;
    }

    /**
     * 取得分页参数
     *
     * @param int     $defaultPageSize 每页行数(服务端), 默认值: TabbyConsts::PAGE_SIZE_DEFAULT
     * @param string  $numKey          当前页参数名, 默认值: TabbyConsts::PAGE_NUM_KEY
     * @param ?string $sizeKey         每页行数参数名(客户端), 默认值: null, 可以选择: TabbyConsts::PAGE_SIZE_KEY
     *
     * @return mixed
     */
    public function getPages(int $defaultPageSize = TabbyConsts::PAGE_SIZE_DEFAULT, string $numKey = TabbyConsts::PAGE_NUM_KEY, ?string $sizeKey = null)
    {
        $pageNum  = $this->request($numKey . '.ept');
        $pageSize = $sizeKey === null ? 0 : $this->request($sizeKey . '.ept');

        return [empty($pageSize) ? $defaultPageSize : $pageSize, empty($pageNum) ? 1 : $pageNum];
    }

    public function __get($name)
    {
        return $this->request($name);
    }

    public function offsetGet($offset)
    {
        return $this->request($offset);
    }
}
