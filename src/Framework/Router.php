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

use Consts\TabbyConsts;
use Tabby\Tabby;
use Tabby\Utils\StrUtils;

class Router implements \Yaf\Route_Interface
{
    public function __construct()
    {
    }

    /**
     * 组合 Uri
     *
     * @param array $info  数组, 下标 :c代表controller, :a代表action
     * @param array $query 参数, 拼接在url中的 query string
     *
     * @return string
     */
    public function assemble($info, $query = null): string
    {
        // yaf处理: '/'替换成''
        $prefix = Tabby::$YAF_REQ->getBaseUri();

        if (isset($info[':c'])) {
            $uriArray = explode('_', $info[':c']);
            foreach ($uriArray as &$v) {
                $v = StrUtils::uncamelize($v);
            }
            $uri = $prefix . '/' . implode('/', $uriArray);
        } else {
            $uri = $prefix . '/' . TabbyConsts::ROUTE_CONTROLLER_DEFAULT;
        }

        if (isset($info[':a'])) {
            $uri .= '/' . StrUtils::uncamelize($info[':a']);
        }

        if ($query !== null) {
            $uri .= '?' . http_build_query($query);
        }

        return $uri;
    }

    /**
     * 路由规则
     *
     * @param \Yaf\Request_Abstract $request
     *
     * @return bool
     */
    public function route($request): bool
    {
        $uri    = strtolower($request->getRequestUri());
        $prefix = $request->getBaseUri();

        // 去前缀
        $len = strlen($prefix);
        if ($prefix !== '' && substr($uri, 0, $len) === $prefix) {
            $uri = substr($uri, $len);
        }

        // 切数组 & 下划线转驼峰(首字母大写)
        $uriArray = array_values(array_filter(
            explode(
                ' ',
                ucwords(
                    str_replace(
                        '/',
                        ' ',
                        str_replace(
                            ' ',
                            '',
                            ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $uri
                                )
                            )
                        )
                    )
                )
            )
        ));

        // URI段数
        $len = count($uriArray);

        // yaf_request.c setControllerName format参数==false 时 有 if (request->controller) 判断
        $request->setControllerName(' ', true);
        $request->setActionName(' ', true);

        // 根据uri段数处理 controller & action , action 首字母小写
        if ($len === 2) {
            $request->setControllerName($uriArray[0], false);
            $request->setActionName(lcfirst($uriArray[1]), false);
        } elseif ($len === 0) {
            $request->setControllerName(TabbyConsts::ROUTE_CONTROLLER_DEFAULT, false);
            $request->setActionName(TabbyConsts::ROUTE_ACTION_DEFAULT, false);
        } elseif ($len === 1) {
            $request->setControllerName($uriArray[0], false);
            $request->setActionName(TabbyConsts::ROUTE_ACTION_DEFAULT, false);
        } else {
            $request->setActionName(lcfirst(array_pop($uriArray)), false);
            $request->setControllerName(implode('_', $uriArray), false);
        }

        return true;
    }
}
