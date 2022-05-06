<?php

use Rules\DemoRules;
use Consts\TabbyConsts;
use Mod\DemoArticleMod;

class Demo_ArticleController extends \Tabby\Framework\Ctrl
{
    public function init()
    {
        // 注册验证规则
        DemoRules::uses();

        // 以json方式返回数据
        \T::$RSP->json();
    }

    /* 分页 搜索 列表 */
    public function listAction($rsp, $req)
    {
        // 取得分页参数
        list($pageSize, $pageNum) = $req->getPages(5, TabbyConsts::PAGE_NUM_KEY, TabbyConsts::PAGE_SIZE_KEY);
        // 搜索条件
        $search = $req['article_search.ept'];
        $search = $search ? ['article_title|like' => "%{$search}%"] : [];

        // 从Model取得分页数据, 包括 总条数 和 当前页列表数据
        $rsp['pageData'] = DemoArticleMod::pageList($pageSize, $pageNum, '*', $search, 'id desc');
    }

    /* 新增 */
    public function createAction($rsp, $req)
    {
        // 调用 Model::create 新增数据
        $rsp['rst'] = DemoArticleMod::create($req);
    }

    /* 更新 */
    public function updateAction($rsp, $req)
    {
        /* ===============================
         * 下面3种 Model 调用方式任选其一:
         * ===============================*/

        // 1. 不写 Model 逻辑, 直接在 Controller 实现 (建议仅在业务逻辑较为简单时使用)
        $rsp['rst1'] = DemoArticleMod::updateById(
            $req['article_id'],
            $req->data([
                'article_title',
                'article_content',
                'article_tag'
            ])
        );

        // 2. 调用 Model::modify 更新数据
        $rsp['rst2'] = DemoArticleMod::modify($req['article_id'], $req);

        // 3. 在 Controller 中对数据进行验证和处理, Model中的判断逻辑不会重复验证(通过识别传入的参数类型)
        $rsp['rst3'] = DemoArticleMod::modify(
            $req['article_id'],
            $req->data([
                'article_title',
                'article_content',
                'article_tag'
            ])
        );
    }

    /* 删除 */
    public function deleteAction($rsp, $req)
    {
        // 调用 Model::delete 删除数据
        $rsp['rst'] = DemoArticleMod::deleteById($req['article_id']);
    }
}
