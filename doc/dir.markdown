# 目录结构

#### 部分目录结构可以通过修改配置调整

conf目录下App对应的 ```.ini```

```ini
tabby.langPath = HOME_PATH "language/zh/"           ;语言包路径
tabby.tplPath = HOME_PATH "app/" APP_NAME "/tpl/"   ;模板路径
tabby.uploadPath = HOME_PATH "static/"              ;UPLOAD文件路径
tabby.tmpPath = HOME_PATH "tmp/"                    ;临时文件夹
application.bootstrap = HOME_PATH "app/" APP_NAME "/" "Bootstrap.php"   ;Bootstrap路径(绝对路径)
application.library = HOME_PATH "src"                                   ;本地(自身)类库的绝对目录地址
```

```
├── app // Apps, 一个项目可能存在多个App, 例如: front,admin,console...
│   └── app_name          // app目录 例如: admin
│       ├── controllers   // controller 在这个目录下
│       ├── public 
│       │   └── index.php // 一般只放一个index.php作为入口文件
│       ├── tpl           // 模板
│       └── Bootstrap.php // 引导代码
├── conf     // 各种配置文件
├── language // 语言包
│   └── zh   // 中文
├── src      // 项目代码
│   ├── Consts // 常量
│   ├── Enums  // 枚举&词典
│   ├── Mod    // Model
│   ├── Plugins// Aop 
│   ├── Rules  // 验证器规则
│   └── Svc    // 业务逻辑
├── support    // 项目辅助文件 例如: sql|脚本...
│   ├── sql
│   └── sh
├── tests  // 单元测试
├── tmp    // 临时文件目录 (要开写权限)
└── vendor // composer...
```