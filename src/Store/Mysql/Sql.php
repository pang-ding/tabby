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

class Sql extends AbstractSql
{
    const SQL_TYPE        = 'sql';
    protected $_forMaster = false;

    public function __construct($sql)
    {
        $this->_sql = $sql;
    }

    /**
     * 执行 Sql 语句, 返回结果集
     *
     * @param mixed $values
     *
     * @return array
     */
    public function fetchAll($values = []): array
    {
        $this->bindValues($values);
        $rst = $this->getConn()->fetchAll($this->build(), $this->_values);

        return is_array($rst) ? $rst : [];
    }

    /**
     * 执行 Sql 语句, 返回一行数据
     *
     * @param mixed $values
     *
     * @return array|false
     */
    public function fetchRow($values = [])
    {
        $this->bindValues($values);

        return $this->getConn()->fetchRow($this->build(), $this->_values);
    }

    /**
     * 执行 Sql 语句, 返回数据
     *
     * @param mixed $values
     *
     * @return mixed
     */
    public function fetchValue($values = [])
    {
        $this->bindValues($values);

        return $this->getConn()->fetchValue($this->build(), $this->_values);
    }

    /**
     * 执行 Sql 语句, 返回 Iterator
     *
     * @param mixed $values
     *
     * @return \Iterator
     */
    public function yield($values = [])
    {
        $this->bindValues($values);

        return $this->getConn()->yield($this->build(), $this->_values);
    }

    /**
     * SQL FOR_UPDATE
     *
     * @param bool $forUpdate
     *
     * @return static
     */
    public function forUpdate(bool $forUpdate = true)
    {
        $this->_forMaster = $forUpdate;

        return $this;
    }

    /**
     * 返回CONN,根据forUpdate判断主从
     *
     * @return Conn
     */
    protected function getConn()
    {
        return $this->_forMaster ? $this->getMaster() : $this->getSlave();
    }

    protected function build(): string
    {
        return $this->_sql;
    }
}
