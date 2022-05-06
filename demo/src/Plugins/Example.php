<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugins;

class Templet extends \Tabby\Framework\PluginAbstract
{
    /**
     * 路由之前,第一个钩子
     * ! 不要在这个钩子里做 $this->assert 判断
     * 在此之前Yaf做的事情：实例化:config、application、response、request、plugins ...
     */
    public function routerStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
    }

    /**
     * 路由完成后
     */
    public function routerShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        // 根据 Controller / Action 判断是否执行
        // if ($this->assert(
        //     [
        //         'Login' => '*',                  // LoginController 的所有 Action 执行
        //         'User'  => ['create', 'update'], // UserController 的 createAction 和 updateAction 执行
        //     ]
        // )) {
        //     //...
        // }
    }

    /**
     * 分发循环开始之前
     * 登录权限检查在这里做
     */
    public function dispatchLoopStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
    }

    /**
     * 分发(Action执行)之前,可能触发多次
     */
    public function preDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
    }

    /**
     * 分发(Action执行)之后,可能触发多次
     */
    public function postDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
    }

    /**
     * 分发循环结束之后,最后一个钩子
     * 可以输出日志记录、处理输出数据
     */
    public function dispatchLoopShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
    }
}
