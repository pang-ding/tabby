<?php

use Mod\DemoTagMod;
use Mod\DemoArticleMod;
use Rules\DemoRules;

class Demo_CrudController extends \Tabby\Framework\Ctrl
{
    public function init()
    {
    }

    public function indexAction($rsp, $req)
    {
        // Mysql连接 & 数据表 检查
        $dbStatus        = $this->initDb();
        $rsp['dbStatus'] = $dbStatus;

        if ($rsp['dbStatus']['conn'] && $rsp['dbStatus']['tables']) {
            // 以KV字典形式返回tags
            $rsp['tags'] = DemoTagMod::dict('tag_key', 'tag_title', ['enable'=>1]);
        }

        $rsp->tpl('demo/crud');
    }

    protected function initDb()
    {
        $tableExists = function ($table) {
            $tables = DI::DB()->sql('show tables')->fetchAll();
            foreach ($tables as $t) {
                if (array_pop($t) === $table) {
                    return true;
                }
            }

            return false;
        };

        $cSql = [
            'tabby_demo_article' => "
            CREATE TABLE IF NOT EXISTS `tabby_demo_article` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `article_title` varchar(32) DEFAULT NULL,
                `article_content` varchar(255) DEFAULT NULL,
                `article_tag` varchar(16) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            INSERT INTO `tabby_demo_article` VALUES (1, '测试数据1-Title', '测试数据1-Content', 'tag1');
            INSERT INTO `tabby_demo_article` VALUES (2, '测试数据2-Title', '测试数据2-Content', 'tag2');
            INSERT INTO `tabby_demo_article` VALUES (3, '测试数据3-Title', '测试数据3-Content', 'tag1');
            INSERT INTO `tabby_demo_article` VALUES (4, '测试数据4-Title', '测试数据4-Content', 'tag2');
            INSERT INTO `tabby_demo_article` VALUES (5, '测试数据5-Title', '测试数据5-Content', 'tag1');
            INSERT INTO `tabby_demo_article` VALUES (6, '测试数据6-Title', '测试数据6-Content', 'tag2');
            ",
            'tabby_demo_tag' => "
            CREATE TABLE IF NOT EXISTS `tabby_demo_tag` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `tag_title` varchar(32) DEFAULT NULL,
                `tag_key` varchar(16) DEFAULT NULL,
                `enable` tinyint(1) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            INSERT INTO `tabby_demo_tag` VALUES (1, 'Tag1', 'tag1', 1);
            INSERT INTO `tabby_demo_tag` VALUES (2, 'Tag2', 'tag2', 1);
            ",
        ];

        $createSql = '';
        $connError = '';
        $connPass  = true;
        $tablePass = true;

        try {
            foreach ($cSql as $k=>$v) {
                if (!$tableExists($k)) {
                    $createSql .= $v;
                    $tablePass = false;
                }
            }
        } catch (\Exception $e) {
            $connPass  = false;
            $connError = $e->getMessage();
        }

        if ($connPass && !$tablePass && $_REQUEST['create_tables']) {
            DI::DB()->sql($createSql)->exec();
            foreach ($cSql as $k=>$v) {
                if (!$tableExists($k)) {
                    throw new ErrorClient("创建 {$k} 表失败, 请尝试自行执行 SQL 创建");
                }
            }
            $tablePass = true;
        }

        return ['conn'=>$connPass, 'tables'=>$tablePass, 'createSql'=>$createSql, 'connError'=>$connError];
    }
}
