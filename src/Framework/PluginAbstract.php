<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework;

abstract class PluginAbstract extends \Yaf\Plugin_Abstract
{
    protected function assert($rule)
    {
        $controller = \Tabby\Tabby::$YAF_REQ->getControllerName();
        if (!isset($rule[$controller])) {
            return false;
        }
        if ($rule[$controller] === '*') {
            return true;
        }
        $action = \Tabby\Tabby::$YAF_REQ->getActionName();
        if (is_string($rule[$controller])) {
            return $rule[$controller] === $action;
        }

        // 不对 $rule[$controller] 做类型检查, 开发者自己保证
        return in_array($action, $rule[$controller]);
    }
}
