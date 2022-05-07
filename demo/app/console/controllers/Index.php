<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class IndexController extends \Tabby\Framework\Ctrl
{
    public function init()
    {
        Vali::mergeRules(
            [
                'foo'=> 'str|between:1,10',
                'bar'=> 'str|between:1,10',
            ]
        );
    }

    public function indexAction(\Tabby\Framework\Request\CliRequest $req)
    {
        for ($i=1; $i <= 2; $i++) {
            \T::$Log->info("FOO: '{$req['foo.ept']}', BAR: '{$req['bar.ept']}'\n");
            echo  "FOO: '{$req['foo.ept']}', BAR: '{$req['bar.ept']}'\n";
            sleep(1);
        }
    }
}
