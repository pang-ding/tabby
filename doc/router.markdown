# 路由 

默认只提供基于规则的路由

### 路由规则
| Uri               | Controller             | Action          | ControllerPath (controllers/) | explain                    |
| ----------------- | ---------------------- | --------------- | ----------------------------- | -------------------------- |
| /                 | indexController        | indexAction     | Index.php                     | 默认 C & A                 |
| /foo              | FooController          | indexAction     | Foo.php                       | 默认 A                     |
| /foo/bar          | FooController          | barAction       | Foo.php                       | 基本情况                   |
| /dir/foo/bar      | Dir_FooController      | barAction       | Dir/Foo.php                   | 目录                       |
| /path/dir/foo/bar | Path_Dir_FooController | barAction       | Path/Dir/Foo.php              | 多层目录                   |
| /under_line/bar   | UnderLineController    | barAction       | UnderLine.php                 | 下划线转驼峰               |
| /foo/under_line   | FooController          | underLineAction | Foo.php                       | 驼峰可以用于action或目录中 |

### 前缀

在 ```app.ini``` 中配置

```ini
;URI前缀, 例如: "/admin", 路由处理时会去掉 Uri 中的前缀部分. /admin/user/create 将指向UserController::createAction
application.baseUri = "/"  
```