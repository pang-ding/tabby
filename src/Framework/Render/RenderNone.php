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

class RenderNone extends RenderAbstract
{
    public static function display(\Tabby\Framework\Response $response)
    {
    }

    public static function exception(\Throwable $exception): void
    {
    }
}
