<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Consts;

class TabbyConsts
{
    // 默认路由
    const ROUTE_CONTROLLER_DEFAULT = 'Index';
    const ROUTE_ACTION_DEFAULT     = 'index';

    // 模板
    const TPL_EXTENSION = 'php';
    const TPL_ERROR     = 'error';

    // 输出
    const JSONP_CALLBACK_NAME = 'callback';

    // 异常
    const ERROR_DEFAULT_MSG   = 'Server Error!';
    const ERROR_SYS_CODE      = 500;
    const ERROR_SYS_TYPE      = 'sys';
    const ERROR_DATA_CODE     = 501;
    const ERROR_DATA_TYPE     = 'data';
    const ERROR_ASSERT_CODE   = 501;
    const ERROR_ASSERT_TYPE   = 'assert';
    const ERROR_CLIENT_CODE   = 400;
    const ERROR_CLIENT_TYPE   = 'client';
    const ERROR_INPUT_CODE    = 401;
    const ERROR_INPUT_TYPE    = 'input';
    const ERROR_NOTFOUND_CODE = 404;
    const ERROR_NOTFOUND_TYPE = 'notfound';

    // MODEL
    const MOD_PAGE_LIST_FIELD  = 'list';
    const MOD_PAGE_TOTAL_FIELD = 'total';

    // 语言包
    const LANG_PKE_ERROR              = 'error';
    const LANG_KEY_ERROR_DEFAULT      = 'default_system';
    const LANG_PKG_ASSERT             = 'assert';
    const LANG_KEY_ASSERT_ERROR_COUNT = 'error_count';
    const LANG_KEY_ASSERT_DEFAULT     = 'default';
    const LANG_PKG_ASSERT_CUSTOM      = 'assert_custom';
    const LANG_PKG_FIELD              = 'field';

    // 验证器默认参数
    const VALIDATOR_FLAG_DEFAULT = 0;

    // 日期时间空值
    const DATETIME_VALUE_EMPTY = '';

    // 翻页
    const PAGE_NUM_KEY      = 'page_num';
    const PAGE_SIZE_KEY     = 'page_size';
    const PAGE_SIZE_DEFAULT = 20;
}
