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

use Tabby\Error\ErrorSys;
use Tabby\Validator\Data;

abstract class AbstractRequest implements \ArrayAccess
{
    protected static $_ins = null;

    protected $_assertData = [];

    abstract public function offsetGet($offset);

    abstract public function data(array $ruleKeys, bool $assertAll = false): Data;

    abstract public function exists(string $key): bool;

    protected function __construct()
    {
    }

    /**
     * @return AbstractRequest
     */
    public static function getIns(): AbstractRequest
    {
        if (static::$_ins === null) {
            static::$_ins = new static();
        }

        return static::$_ins;
    }

    public function offsetExists($offset): bool
    {
        return $this->exists($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new ErrorSys('Request Error: Read only (key:' . $offset . ')');
    }

    public function offsetUnset($offset): void
    {
        throw new ErrorSys('Request Error: Read only (key:' . $offset . ')');
    }
}
