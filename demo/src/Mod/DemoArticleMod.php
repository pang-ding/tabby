<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mod;

class DemoArticleMod extends \Tabby\Mod\AbstractMod
{
    use \Tabby\Mod\Traits\Enable;

    // Model对应的表名
    protected static $_TABLE_NAME = 'tabby_demo_article';

    // 实现 Create 方法
    public static function create($data)
    {
        // 检查数据, 同时确定需要添加的字段
        $data = \Vali::assertInputData(
            $data,
            [
                'article_title',
                'article_content',
                'article_tag',
            ]
        );

        // 调用父类的方法 insert 到数据库
        return static::insert($data);
    }

    public static function modify(int $id, $data)
    {
        // 这里的 assertSysData 和 create方法中的 assertInputData 区别在于是否将错误信息抛给用户
        $data = \Vali::assertSysData(
            $data,
            [
                'article_title',
                'article_content',
                'article_tag',
            ]
        );

        return static::updateById($id, $data);
    }
}
