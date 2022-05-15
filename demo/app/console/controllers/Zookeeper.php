<?php

use Tabby\Middleware\Zookeeper\ZK;

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
