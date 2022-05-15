<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Zookeeper;

class ZK
{
    private ?\Zookeeper $_zk = null;
    private string $_host;
    private int $_recvTimeout;
    private string $_scheme;
    private string $_cert;

    /**
     *
     * @param string $host        '127.0.0.1:2181,127.0.0.1:2182,127.0.0.1:2183'
     * @param int    $recvTimeout 默认: 10000
     * @param string $scheme      'digest'
     * @param string $cert        'tabby_test:tabby_test'
     */
    public function __construct(string $host, int $recvTimeout = 10000, string $scheme = '', string $cert = '')
    {
        $this->_host        = $host;
        $this->_recvTimeout = $recvTimeout;
        $this->_scheme      = $scheme;
        $this->_cert        = $cert;
    }

    /**
     * Connect
     *
     * @return \Zookeeper
     */
    public function connect(): \Zookeeper
    {
        if (!$this->_zk) {
            $this->_zk = new \Zookeeper($this->_host, null, $this->_recvTimeout);
            if ($this->_scheme) {
                $this->_zk->addAuth($this->_scheme, $this->_cert);
            }
        }

        return $this->_zk;
    }

    /**
     * Close
     *
     */
    public function close(): void
    {
        $this->_zk->close();
        $this->_zk = null;
    }

    /**
     * Get
     *
     * @param string        $path
     * @param callable|null $watcher function($state, $event, $eventPath); event: \Zookeeper::CHANGED_EVENT = 3
     *
     * @return string
     */
    public function get(string $path, ?callable $watcher = null): string
    {
        return $this->connect()->get($path, $watcher);
    }

    /**
     * Set
     *
     * @param string $path
     * @param string $value
     *
     * @return bool
     */
    public function set(string $path, ?string $value): bool
    {
        return $this->connect()->set($path, $value);
    }

    /**
     * Create
     *
     * @param string      $path
     * @param string|null $value
     * @param array       $aclArray  [['perms'  => \Zookeeper::PERM_ALL, 'scheme' => 'auth', 'id' => 'tabby_test:tabby_test']] || [['perms'  => \Zookeeper::PERM_ALL, 'scheme' => 'world, 'id' => 'anyone']]
     * @param bool        $ephemeral 是否临时节点 默认: false
     * @param bool        $sequence  是否自增节点 默认: false
     *
     * @return string
     */
    public function create(string $path, ?string $value, array $aclArray, bool $ephemeral = false, bool $sequence = false): string
    {
        $flags = 0;
        $ephemeral && $flags += \Zookeeper::EPHEMERAL;
        $sequence && $flags += \Zookeeper::SEQUENCE;

        return $this->connect()->create($path, $value, $aclArray, $flags);
    }

    /**
     * Delete
     *
     * @param string $path
     * @param int    $version
     *
     * @return bool
     */
    public function delete(string $path, int $version = -1): bool
    {
        return $this->connect()->delete($path, $version);
    }

    /**
     * Exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->connect()->exists($path);
    }
}
