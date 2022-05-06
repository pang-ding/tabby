<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Validator;

use Consts\TabbyConsts;
use Tabby\Error\ErrorSys;
use Tabby\Framework\Language;

/**
 * 验证规则宿主
 */
class Rules implements \ArrayAccess
{
    const FLAG_SEPARATOR = '.';

    const FLAG_STR = [
        'ept'    => Validator::FLAG_EMPTY,
        'null'   => Validator::FLAG_NULL,
        'arr'    => Validator::FLAG_ARRAY,
        'notrim' => Validator::FLAG_OFF_TRIM,
        'noxss'  => Validator::FLAG_OFF_XSS,
        'nofmt'  => Validator::FLAG_OFF_FORMAT,
    ];

    /**
     * 规则
     *
     * @var array
     */
    protected $_rules;

    /**
     * 自定义错误信息
     *
     * @var array
     */
    protected $_customMsg;

    /**
     *
     * @param array $rules
     */
    public function __construct(array $rules = [], array $customMsg = [])
    {
        $this->_rules     = $rules;
        $this->_customMsg = $customMsg;
    }

    public function offsetSet($offset, $value): void
    {
        $this->_rules[$offset] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->_rules[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->_rules[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_rules[$offset];
    }

    public function merge(array $rules): void
    {
        $this->_rules = array_merge($this->_rules, $rules);
    }

    public function mergeCustomMsg(array $customMsg): void
    {
        $this->_customMsg = array_merge($this->_customMsg, $customMsg);
    }

    /**
     * 格式化异常信息
     *
     * @param string     $key        Rule key
     * @param string     $assertName 发生问题的断言名称
     * @param array|null $msgValues  断言输出的错误参数
     * @param string     $msg        调用者给出的错误信息(优先使用)
     *
     * @return string
     */
    public function formatMsg(string $key, string $assertName, ?array $msgValues = null, string $msg = ''): string
    {
        if ($msg !== '') {
            return $msg;
        }
        $vkey = "{$key}.{$assertName}";
        if (isset($this->_customMsg[$vkey])) {
            return Language::getIns()->change($this->_customMsg[$vkey], $msgValues);
        }
        if (isset($this->_customMsg[$key])) {
            return Language::getIns()->change($this->_customMsg[$key]);
        }

        return Language::getMsg(TabbyConsts::LANG_PKG_FIELD, $key, null, $key) . ' ' .
        Language::getMsg(TabbyConsts::LANG_PKG_ASSERT, $assertName, $msgValues, $this->getDefaultMsg());
    }

    /**
     * 默认错误信息
     *
     * @return string
     */
    protected function getDefaultMsg(): string
    {
        $defaultMsg = Language::getMsg(TabbyConsts::LANG_PKG_ASSERT, TabbyConsts::LANG_KEY_ASSERT_DEFAULT, null, '');
        if ($defaultMsg === '') {
            throw new ErrorSys('Validator Error: Default message not found');
        }

        return $defaultMsg;
    }

    /**
     * 剥离 $rule 串中的 flag 参数部分, 并返回int型 flag
     *
     * @param string &$rule
     */
    public static function stripFlagByRuleStr(string &$rule): int
    {
        $fArr = explode(self::FLAG_SEPARATOR, trim($rule));
        if (count($fArr) === 1) {
            $rule = $fArr[0];

            return 0;
        }
        $rule = array_shift($fArr);
        $flag = 0;
        foreach ($fArr as $f) {
            if (!isset(self::FLAG_STR[$f])) {
                throw new ErrorSys('Rules Error: Unknown rule flag: ' . $f);
            }
            $flag += self::FLAG_STR[$f];
        }

        return $flag;
    }

    /**
     * 格式化Flag 输出int型
     *
     * @param mixed $flag
     *
     * @return int
     */
    public static function normalizeFlag($flag = null): int
    {
        if (is_int($flag)) {
            return $flag;
        }
        if ($flag === null) { // 0 和 '' 不会设成默认值
            return TabbyConsts::VALIDATOR_FLAG_DEFAULT;
        }
        if (!is_string($flag)) {
            throw new ErrorSys('Rules Error: NormalizeFlag() argument type must be one of string or int');
        }
        $fArr = explode(self::FLAG_SEPARATOR, trim($flag));
        $flag = 0;
        foreach ($fArr as $f) {
            $f = trim($f);
            if ($f !== '') {
                if (!isset(self::FLAG_STR[$f])) {
                    throw new ErrorSys("Rules Error: Unknown flag '{$f}'");
                }
                $flag += self::FLAG_STR[$f];
            }
        }

        return $flag;
    }
}
