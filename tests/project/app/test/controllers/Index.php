<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tabby\Test\Context;

class IndexController extends Ctrl
{
    private $v = '';

    public function init()
    {
        \Vali::mergeRules(['test' => 'str']);
        $this->v = 'inited';
    }

    public function testTestAction($rsp, $req)
    {
        Context::$value1 .= __METHOD__ . '/';
        Context::$value1 .= $req['test'] . '/';

        $rsp->echo(__METHOD__);
        $this->forward('Index', 'test1');
    }

    public function test1Action($rsp, $req)
    {
        Context::$value1 .= __METHOD__ . '/';
        Context::$value1 .= $this->v . '/';

        $rsp->echo(__METHOD__);
    }

    public function errorAction($rsp, $req)
    {
        Context::$value1 .= __METHOD__ . '/';
        throw new \Exception(Context::$value2);
    }
}
