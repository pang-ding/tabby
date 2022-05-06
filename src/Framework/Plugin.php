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

use Tabby\Tabby;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

class Plugin extends \Yaf\Plugin_Abstract
{
    /**
     * 路由之前,第一个钩子
     * 在此之前Yaf做的事情：实例化:config、application、response、request、plugins
     * 可以做url重写、请求日志记录等功能
     */
    public function routerStartup(Request_Abstract $request, Response_Abstract $response)
    {
        // \T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . \Tabby\Utils\Random::string(10));
    }

    public function routerShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        // \T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . \Tabby\Utils\Random::string(10));
    }

    public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response)
    {
        // \T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . \Tabby\Utils\Random::string(10));
    }

    /**
     * 分发(Action执行)之前,可能触发多次
     * 可以做登陆检测等功能
     */
    public function preDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        // \T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . \Tabby\Utils\Random::string(10));
        //注册Action参数
        $request->setParam('req', Tabby::$REQ);
        $request->setParam('rsp', Tabby::$RSP);
    }

    /**
     * 分发(Action执行)之后,可能触发多次
     */
    public function postDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        // \T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . \Tabby\Utils\Random::string(10));
    }

    /**
     * 分发循环结束之后,最后一个钩子
     * 此时所有的业务逻辑都已经运行完成,但响应还没有发送
     * 可以做输出日志记录、处理输出数据等功能
     */
    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        // \T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . \Tabby\Utils\Random::string(10));
    }
}
