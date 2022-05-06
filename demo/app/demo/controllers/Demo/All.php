<?php

class Demo_AllController extends \Tabby\Framework\Ctrl
{
    public function init()
    {
    }

    public function indexAction($rsp, $req)
    {
        $this->redirect('/demo/all/io');
    }

    public function ioAction($rsp, $req)
    {
        // 注册验证规则, 实际项目中一般放在 rules 目录下, 统一管理全局使用
        Vali::mergeRules(
            [
                'foo'=> 'str|between:2,10',
                'bar'=> 'int|between:1,100'
            ]
        );

        // 获取输入信息
        $foo = $req['foo.ept']; // .ept 表示允许为空
        $bar = $req['bar.ept'];

        // 输出数据
        $rsp['input'] = "foo='{$foo}', bar='{$bar}'";
        $rsp['foo']   = $foo;
        $rsp['bar']   = $bar;

        // 输出到模板 [demo/io.php]
        $rsp->tpl('demo/io');

        // 还可以:
        // $rsp->json();
        // $rsp->jsonp();
        // $rsp->echo('...');
        // 以及自定义输出方式
    }
}
