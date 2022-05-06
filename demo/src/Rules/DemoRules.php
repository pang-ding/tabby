<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rules;

use Mod\DemoTagMod;
use Mod\DemoArticleMod;

class DemoRules extends AbstractRules
{
    // 验证规则: 在这里可以 自定义 验证方法 及 报错信息
    protected static function data()
    {
        return [
            'article_id'      => 'str|hasid:' . DemoArticleMod::class, // ID是否存在
            'article_title'   => 'str|between:1,16',
            'article_content' => 'str|between:1,255',
            'article_tag'     => 'str|hasval:' . DemoTagMod::class . ',tag_key', // 标签是否存在
            'article_search'  => 'str|between:1,16',
        ];
    }
}
