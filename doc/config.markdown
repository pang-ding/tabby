# 配置


### Tabby 配置分别放置于:
1) conf目录下 app对应的 .ini 文件 (项目入口 index.php 中加载了该文件)
2) /src/Consts/TabbyConsts.php 文件中的常量

```app.ini```

```php
// ini配置信息可以通过 \T::$Conf 在项目中获取
\T::$Conf->get('app.path');
```

```ini
[conf]

;# SYSTEM
prj.name = tabbyhome    ;项目名
prj.path = HOME_PATH    ;项目路径
app.name = main         ;APP名

app.path = HOME_PATH "app/" APP_NAME "/"    ;APP路径

;# Tabby
tabby.langPath = HOME_PATH "language/zh/"           ;语言包路径
tabby.tplPath = HOME_PATH "app/" APP_NAME "/tpl/"   ;模板路径
tabby.uploadPath = HOME_PATH "static/"              ;#UPLOAD文件路径
tabby.tmpPath = HOME_PATH "tmp/"                    ;临时文件夹
tabby.isCli = false                                 ;CLI模式
tabby.isDebug = false                               ;Debug模式

;##ENV
;env.zdaemon = /usr/local/bin/zdaemon               ;python zdaemon 路径

;##LOG
log.level = info

;#YAF
application.bootstrap = HOME_PATH "app/" APP_NAME "/" "Bootstrap.php"   ;Bootstrap路径(绝对路径)
application.directory = HOME_PATH "app/" APP_NAME "/"                   ;APP路径
application.library = HOME_PATH "src"                                   ;本地(自身)类库的绝对目录地址
;URI前缀, 例如: "/admin", 路由处理时会去掉 Uri 中的前缀部分. /admin/user/create 将指向UserController::createAction
application.baseUri = "/"                                               

; 下面的不要改
application.dispatcher.throwException = true                            ;在出错的时候, 是否抛出异常
;//是否使用默认的异常捕获Controller, 如果开启, 在有未捕获的异常的时候, 控制权会交给ErrorController的errorAction方法, 可以通过$request->getException()获得此异常对象
application.dispatcher.catchException = true
application.system.use_spl_autoload = false                             ;启用SPL autoload

;#MYSQL
mysql.host     = "127.0.0.1"
mysql.username = ""
mysql.password = ""
mysql.port     = 3306
mysql.dbname   = ""
```

```TabbyConsts.php```

```php
class TabbyConsts
{
    // 默认路由
    const ROUTE_CONTROLLER_DEFAULT = 'Index';
    const ROUTE_ACTION_DEFAULT     = 'index';

    // 模板
    const TPL_EXTENSION = 'php';        // 模板扩展名
    const TPL_ERROR     = 'error';      // 异常页模板

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
    const MOD_PAGE_LIST_FIELD  = 'list';    // pageList()方法返回值 列表数据下标
    const MOD_PAGE_TOTAL_FIELD = 'total';   // pageList()方法返回值 总行数下标

    // 语言包
    const LANG_PKE_ERROR              = 'error';
    const LANG_KEY_ERROR_DEFAULT      = 'default_system';   // 默认异常信息Key
    const LANG_PKG_ASSERT             = 'assert';           // 验证器, 异常包
    const LANG_KEY_ASSERT_ERROR_COUNT = 'error_count';      // 错误条数Key, 返回全部错误信息时使用
    const LANG_KEY_ASSERT_DEFAULT     = 'default';          // 验证器默认异常信息Key
    const LANG_PKG_ASSERT_CUSTOM      = 'assert_custom';    // 自定义异常信息包
    const LANG_PKG_FIELD              = 'field';            // 字段名包

    // 验证器默认参数
    const VALIDATOR_FLAG_DEFAULT = 0;

    // 日期时间空值
    const DATETIME_VALUE_EMPTY = '';

    // 翻页
    const PAGE_NUM_KEY      = 'page_num';
    const PAGE_SIZE_KEY     = 'page_size';
    const PAGE_SIZE_DEFAULT = 20;
}

```