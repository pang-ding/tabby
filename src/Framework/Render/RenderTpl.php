<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework\Render;

use Consts\TabbyConsts;
use Tabby\Framework\Render\Drivers\DriverAbstract;

class RenderTpl extends RenderAbstract
{
    /**
     * @var DriverAbstract
     */
    protected static $_driver;
    protected static $_tpl = '';

    public static function redirect($url)
    {
        header("Location: {$url}");

        echo '<a href="' . $url . '" id="r">Click here: ' . $url . '</a><script>document.getElementById("r").click();</script>';
    }

    public static function setDriver(DriverAbstract $driver): void
    {
        self::$_driver = $driver;
    }

    public static function setTpl(string $tpl): void
    {
        self::$_tpl = $tpl;
    }

    public static function display(\Tabby\Framework\Response $response)
    {
        header('Content-type: text/html;charset=utf-8');
        $data = $response->getAll();
        self::$_driver->prepare($data);
        self::$_driver->addData($data);
        self::$_driver->render(self::$_tpl);
    }

    public static function exception(\Throwable $exception): void
    {
        header('Content-type: text/html;charset=utf-8');
        $data = [
            'errorData' => self::formatErrorData($exception),
        ];
        if (\T::$isDebug) {
            $data['exception'] = $exception;
        }
        self::$_driver->prepare($data);
        self::$_driver->addData($data);
        self::$_driver->render(TabbyConsts::TPL_ERROR);
    }
}
