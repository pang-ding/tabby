# Cli & Daemon

#### 参考:  /demo/app/console/

#### 运行
```sh
# -r "index/index" 等同于Uri, 决定路由
# -d "foo=1&bar=2" 参数, 可以通过 Request 获取
php -c ../../conf/php_dev.ini ./entry.php -r "index/index" -d "foo=1&bar=2"
```

#### 守护进程 (依赖 Python zdaemon, 安装教程: Baidu or Google)
```sh
# 启动:
./daemon_start.sh "/index/index" "foo=a&bar=b"
# 全部停止:
./daemon_kill.sh
```