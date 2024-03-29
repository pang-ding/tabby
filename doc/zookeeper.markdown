# Zookeeper

#### 参考 DEMO: /demo/app/console/controllers/Zookeeper.php

```sh
# 创建节点:
php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/create" -d "path=/tabby_test"

# SET
php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/set" -d "path=/tabby_test&value=abc"

# GET
php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/get" -d "path=/tabby_test"

# WATCH
php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/watch" -d "path=/tabby_test"

# WATCH 后 新开一个终端 执行 SET, LOG中可以看到捕获到的信息
```

```php
class ZookeeperController extends \Tabby\Framework\Ctrl
{
    private $_zk;
    private $_watchPath;

    public function init()
    {
        Vali::mergeRules(
            [
                'path'  => 'str|between:1,30',
                'value' => 'str|between:1,30',
            ]
        );
        $this->_zk = new ZK('127.0.0.1:2181,127.0.0.1:2182,127.0.0.1:2183', 10000, 'digest', 'tabby_test:tabby_test');
    }

    // php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/create" -d "path=/tabby_test"
    public function createAction(\Tabby\Framework\Request\CliRequest $req)
    {
        $rst = $this->_zk->create(
            $req->path,
            null,
            [[
                'perms'  => \Zookeeper::PERM_ALL,
                'scheme' => 'auth',
                'id'     => 'tabby_test:tabby_test',
            ]],
        );
        dump($rst);
    }

    // php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/set" -d "path=/tabby_test&value=abc"
    public function setAction(\Tabby\Framework\Request\CliRequest $req)
    {
        $rst = $this->_zk->set($req->path, $req->value);
        dump($rst);
    }

    // php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/get" -d "path=/tabby_test"
    public function getAction(\Tabby\Framework\Request\CliRequest $req)
    {
        $rst = $this->_zk->get($req->path);
        dump($rst);
    }

    // php -c ./conf/php.ini ./app/console/entry.php -r "/zookeeper/watch" -d "path=/tabby_test"
    public function watchAction(\Tabby\Framework\Request\CliRequest $req)
    {
        $this->_watchPath = $req->path;
        $this->watch(0, 0, '');
        while (true) {
            sleep(1);
        }
    }

    public function watch($state, $event, $eventPath)
    {
        \T::$Log->info("STATE: '{$state}' | EVENT: '{$event}' | EVENT_PATH: '{$eventPath}'");

        $rst = $this->_zk->get($this->_watchPath, [$this, 'watch']);
        \T::$Log->info("WATCH_GET: '$rst'");
    }
}
```

Doc:

<https://tabby.lvliangyu.com/article?channel=tabby_doc&category=middleware&article_id=628123c002eafa6913071dd2>