# 安装

环境要求: 
* php: ^7.4 
* yaf: ^3.3

Yaf 安装:

```sh
pecl install yaf
```

项目目录下执行:

```sh
composer require pang-ding/tabby
```

修改 ___composer.json___ 文件:

```json
{
    "require": {
        "pang-ding/tabby": "*"
    },
    "autoload": {
        "psr-4": {
            "SystemApp\\": "app"
        },
        "files": [
            "src/helpers.php"
        ]
    }
}
```

更新 composer

```sh
composer update
```

启动测试环境 (php -S, 端口自己改), 在服务器上可以用IDE端口转发

```sh
./demo.sh
```