<?php

class IndexController extends \Tabby\Framework\Ctrl
{
    public function init()
    {
    }

    public function indexAction($rsp, $req)
    {
        $rsp->tpl('demo/index');
    }
}
