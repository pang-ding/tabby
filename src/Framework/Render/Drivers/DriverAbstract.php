<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework\Render\Drivers;

abstract class DriverAbstract
{
    /**
     * @var callable
     */
    protected $_prepare = null;

    abstract public function render(string $tpl): void;

    abstract public function addData(array $data): void;

    public function setPrepare(callable $prepare): void
    {
        $this->_prepare = $prepare;
    }

    public function prepare(array &$data): void
    {
        if ($this->_prepare) {
            ($this->_prepare)($data);
        }
    }
}
