<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Store\Mysql;

use Tabby\Error\ErrorSys;

class DB
{
    /**
     * 数据库连接实例 (主库)
     *
     * @var Conn
     */
    protected $_master = null;

    /**
     * 数据库连接实例 (从库)
     *
     * @var Conn
     */
    protected $_slave = null;

    /**
     * 是否执行事务
     *
     * @var bool
     */
    protected $_inTrans = false;

    public function __construct(Conn $master, ?Conn $slave = null)
    {
        $this->_master = $master;

        if (is_array($slave)) {
            $slave = count($slave) > 0 ? $slave[array_rand($slave)] : null;
        }

        if ($slave instanceof Conn) {
            $this->_slave = $slave;
        }
    }

    /**
     * 启动一个事务
     *
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
     */
    public function begin(): bool
    {
        $this->_inTrans = true;

        return $this->getMaster()->begin();
    }

    /**
     * 提交一个事务
     *
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE。
     */
    public function commit(): bool
    {
        $rst = $this->getMaster()->commit();
        // if ($rst) {
        //     $this->_inTrans = false;
        // }

        return $rst;
    }

    /**
     * 回滚一个事务
     *
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
     */
    public function rollBack(): bool
    {
        $rst = $this->getMaster()->rollBack();
        // if ($rst) {
        //     $this->_inTrans = false;
        // }

        return $rst;
    }

    /**
     * 创建 Sql 对象
     *
     * @param string $sql
     *
     * @return Sql
     */
    public function sql($sql): Sql
    {
        $sql = new Sql($sql);
        $sql->setDB($this);

        return $sql;
    }

    /**
     * 创建 Select 对象
     *
     * @param mixed $select
     *
     * @return Select
     */
    public function select($select = '*'): Select
    {
        $select = new Select($select);
        $select->setDB($this);

        return $select;
    }

    /**
     * 创建 Insert 对象
     *
     * @param string $table
     *
     * @return Insert
     */
    public function insert($table): Insert
    {
        $insert = new Insert($table);
        $insert->setDB($this);

        return $insert;
    }

    /**
     * 创建 Update 对象
     *
     * @param string $table
     *
     * @return Update
     */
    public function update($table): Update
    {
        $update = new Update($table);
        $update->setDB($this);

        return $update;
    }

    /**
     * 创建 Delete 对象
     *
     * @param string $table
     *
     * @return Delete
     */
    public function delete($table): Delete
    {
        $delete = new Delete($table);
        $delete->setDB($this);

        return $delete;
    }

    /**
     * 获取 Insert ID
     *
     * @return string|false
     */
    public function lastId()
    {
        return $this->getMaster()->lastInsertId();
    }

    /**
     * 获取主库连接
     *
     * @return Conn
     */
    public function getMaster(): Conn
    {
        if ($this->_master === null) {
            throw new ErrorSys('DB错误: 没有配置 Mysql 主库');
        }

        return $this->_master;
    }

    /**
     * 获取从库连接
     *
     * @return Conn
     */
    public function getSlave(): Conn
    {
        return $this->_slave === null || $this->_inTrans ? $this->getMaster() : $this->_slave;
    }
}
