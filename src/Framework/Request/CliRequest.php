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

use Tabby\Validator\Data;
use Tabby\Validator\Validate as Validator;

class CliRequest extends AbstractRequest implements \ArrayAccess
{
    protected $_data = [];

    public function setData($data)
    {
        $this->_data = is_array($data) ? $data : [];
    }

    /**
     * 获取外部参数
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     * @param string $msg     自定义异常
     *
     * @return mixed
     */
    public function request(string $key, $default = null, string $msg = '')
    {
        return Validator::assertInputValue($this->_data, $key, $default, $msg);
    }

    /**
     * 批量获取外部参数
     *
     * @param array $ruleKeys  变量名数组
     * @param bool  $assertAll 验证全部参数并返回全部错误信息, 默认:false, 遇到一个参数验证失败直接返回错误
     *
     * @return Data
     */
    public function data(array $ruleKeys, bool $assertAll = false): Data
    {
        return Validator::assertInputData($this->_data, $ruleKeys, $assertAll);
    }

    /**
     * 检查是否存在 $key (isset, 不是array_key_exists)
     *
     * @param string $key 参数名
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return isset($this->_data[$key]);
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
