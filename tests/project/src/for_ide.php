<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// 为了解决IDE不识别 class_alias 用的土招,任何情况下都不要 include 这个文件
return;

if (1 == 2) {
    class T extends \Tabby\Tabby
    {
    }

    class DI extends \Tabby\Framework\DI
    {
    }

    class Ctrl extends \Tabby\Framework\Ctrl
    {
    }

    class Vali extends Tabby\Validator\Validate
    {
    }

    class Lang extends \Tabby\Framework\Language
    {
    }

    class ErrorSys extends \Tabby\Error\ErrorSys
    {
    }

    class ErrorClient extends \Tabby\Error\ErrorClient
    {
    }

    class ErrorData extends \Tabby\Error\ErrorData
    {
    }
}
