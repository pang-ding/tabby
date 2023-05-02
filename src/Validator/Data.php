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

use Tabby\Error\ErrorSys;
use Tabby\Utils\ArrayUtils;

class Data extends \ArrayObject
{
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * 合并
     *
     * @param mixed $data
     * @param bool  $throwError
     *
     * @return Data
     */
    public function merge($data, bool $throwError = true): Data
    {
        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $k => $v) {
                $this[$k] = $v;
            }
        } elseif ($throwError) {
            throw new ErrorSys('Validator_Data Error: Merge argument $data must be an array of traversable');
        }

        return $this;
    }

    /**
     * 过滤Null
     *
     * @return Data
     */
    public function unsetNull()
    {
        $dels = [];
        foreach ($this as $k => $v) {
            if (null === $v) {
                $dels[] = $k;
            }
        }
        foreach ($dels as $v) {
            unset($this[$v]);
        }

        return $this;
    }

    /**
     * 过滤空值
     *
     * @return Data
     */
    public function unsetEmpty()
    {
        $dels = [];
        foreach ($this as $k => $v) {
            if (empty($v) && $v !== '0') {
                $dels[] = $k;
            }
        }
        foreach ($dels as $v) {
            unset($this[$v]);
        }

        return $this;
    }

    /**
     * 替换指定的键
     *
     * @return Data
     */
    public function replaceKey(array $replace, bool $keepOrder = false)
    {
        // todo: 这样调用copy次数比较多
        return new static(ArrayUtils::replaceKey($this, $replace, $keepOrder));
    }
}
