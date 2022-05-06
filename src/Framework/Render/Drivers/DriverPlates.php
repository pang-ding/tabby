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

use Consts\TabbyConsts;
use Tabby\Tabby;

class DriverPlates extends DriverAbstract
{
    /**
     * @var \League\Plates\Engine
     */
    protected $_plates;

    public function __construct(\League\Plates\Engine $engine = null)
    {
        if ($engine === null) {
            $engine = new \League\Plates\Engine(Tabby::$Conf['tabby']['tplPath'], TabbyConsts::TPL_EXTENSION);
        }
        $this->_plates = $engine;
    }

    public function getPlates(): \League\Plates\Engine
    {
        return $this->_plates;
    }

    public function render(string $tpl): void
    {
        echo $this->_plates->render($tpl);
    }

    public function addData(array $data): void
    {
        $this->_plates->addData($data);
    }

    public function registerFunction(string $name, callable $callback): void
    {
        $this->_plates->registerFunction($name, $callback);
    }
}
