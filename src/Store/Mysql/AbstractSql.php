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

abstract class AbstractSql
{
    /**
     * 查询类型
     */
    const SQL_TYPE = '';

    /**
     * 反引号
     */
    const QUOTE_CHAR = '`';

    protected $_table   = '';
    protected $_tableAs = '';
    protected $_join    = [];
    protected $_where   = [];
    protected $_orderBy = '';
    protected $_limit   = '';
    protected $_set     = '';

    protected $_sql = '';

    /**
     * 绑定数据
     *
     * @var array
     */
    protected $_values = [];

    /**
     * 绑定数据计数器
     *
     * @var int
     */
    protected $_valuesCounter = 1;

    /**
     * DB
     *
     * @var DB
     */
    protected $_db = null;

    /**
     * 构建 Sql 语句
     * 写入 $this->_sql
     *
     * @return string SQL
     */
    abstract protected function build(): string;

    /**
     * 设置 DB 对象
     *
     * @param DB $host
     */
    public function setDB(DB $host)
    {
        $this->_db = $host;
    }

    /**
     * 执行 Sql 语句, Insert重写了该方法
     *
     * @param ?array $values
     *
     * @return int 受影响行数
     */
    public function exec(?array $values = [])
    {
        if ($values !== []) {
            $this->bindValues($values);
        }

        return $this->getMaster()->execute($this->build(), $this->_values);
    }

    /**
     * 返回 Sql类型
     *
     * @return string
     */
    public function getSqlType(): string
    {
        return static::SQL_TYPE;
    }

    /**
     * 返回 Sql语句
     *
     * @return string
     */
    public function getSql()
    {
        return $this->_sql;
    }

    /**
     * 绑定数据
     *
     * @param array $values
     *
     * @return static
     */
    public function bindValues(array $values)
    {
        if (count($values) !== 0) {
            $this->_values = array_merge($this->_values, $values);
        }

        return $this;
    }

    /**
     * 返回 已绑定数据
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->_values;
    }

    /**
     * SQL WHERE
     *
     * @param mixed  $where
     * @param Bool   $isOr
     * @param string $key   删除时使用
     *
     * @return static
     */
    public function where($where, bool $isOr = false, string $key = '')
    {
        $sqlWhere = $this->formatWhere($where, $isOr);
        if ($sqlWhere !== '') {
            if ($key === '') {
                $this->_where[] = $sqlWhere;
            } else {
                $this->_where[$key] = $sqlWhere;
            }
        }

        return $this;
    }

    /**
     * Unset WHERE
     *
     * @param string $key
     *
     * @return static
     */
    public function unsetWhere(string $key)
    {
        unset($this->_where[$key]);

        return $this;
    }

    protected function formatWhere($where, $isOr = false)
    {
        if (empty($where)) {
            return '';
        }
        if (is_string($where)) {
            return $where;
        }
        $item = [];
        foreach ($where as $k => $v) {
            if (is_integer($k)) {
                $sql = $this->formatWhere($v, !$isOr);
                if ($sql !== '') {
                    $item[] = '(' . $sql . ')';
                }

                continue;
            }
            $isBind = $k[0] === ':';
            if ($isBind) {
                //后期绑定变量
                $k = substr($k, 1);

                if (is_array($v)) {
                    //处理数组,主要用于in或not in
                    //仅限Array不接受其他迭代器
                    $bindItems = [];
                    foreach ($v as $bindItem) {
                        $bindItems[] = ':' . $bindItem;
                    }
                    $bindStr = '(' . implode(', ', $bindItems) . ')';
                } else {
                    $bindStr = ':' . $v;
                }
            } else {
                //绑定变量
                if (is_array($v)) {
                    $bindItems = [];
                    foreach ($v as $valueItem) {
                        $bindItems[] = $this->bind($valueItem);
                    }
                    $bindStr = '(' . implode(', ', $bindItems) . ')';
                } else {
                    //一般流程
                    $bindStr = $this->bind($v);
                }
            }
            $pos = strpos($k, '|');
            if ($pos === false) {
                $item[] = $k . ' = ' . $bindStr;
            } else {
                $item[] = substr($k, 0, $pos) . ' ' . substr($k, $pos + 1) . ' ' . $bindStr;
            }
        }
        $itemNum = count($item);

        switch ($itemNum) {
            case 0:
                return '';
            case 1:
                return $item[0];
            default:
                return implode($isOr ? ' OR ' : ' AND ', $item);
        }
    }

    /**
     * @param bool $allowEmpty 是否允许为空, delete/update场景应该报错
     */
    protected function buildWhere(bool $allowEmpty = false)
    {
        $count = count($this->_where);
        if ($count === 0) {
            if ($allowEmpty) {
                return '';
            } else {
                throw new ErrorSys('SQL Error: where is empty');
            }
        } elseif ($count === 1) {
            return ' WHERE ' . $this->_where[key($this->_where)];
        } else {
            return ' WHERE (' . implode(') AND (', $this->_where) . ')';
        }
    }

    /**
     * SQL SET (INSERT or UPDATE 使用)
     * 批量插入使用multiInsert
     *
     * @param mixed $set 数据KV
     *
     * @return static
     */
    public function set($set)
    {
        $items = [];
        foreach ($set as $k => $v) {
            $items[] = $k . '=' . $this->bind($v);
        }
        $this->_set = ' SET ' . implode(', ', $items);

        return $this;
    }

    /**
     * SQL JOIN
     *
     * @param string $table       要连接的表
     * @param string $col         要连接的表中的关联字段
     * @param string $originTable 原表
     * @param string $originCol   原表中的关联字段
     * @param string $as
     * @param string $key         需要在后续操作中删除这条连接时使用
     * @param string $type        JOIN|LEFT JOIN|RIGHT JOIN
     *
     * @return static
     */
    public function join(string $table, string $col, string $originTable, string $originCol, string $as = '', string $key = '', string $type = 'JOIN')
    {
        $sqlJoin = $type . ' ' . $table;
        if ($as !== '') {
            $sqlJoin .= ' AS ' . $as;
        }
        $sqlJoin .= ' ON ' . $originTable . '.' . $originCol . ' = ' . ($as ?: $table) . '.' . $col;
        if ($key === '') {
            $this->_join[] = $sqlJoin;
        } else {
            $this->_join[$key] = $sqlJoin;
        }

        return $this;
    }

    /**
     * SQL LEFTJOIN
     *
     * @param string $table       要连接的表
     * @param string $col         要连接的表中的关联字段
     * @param string $originTable 原表
     * @param string $originCol   原表中的关联字段
     * @param string $as
     * @param string $key         需要在后续操作中删除这条连接时使用
     *
     * @return static
     */
    public function leftJoin(string $table, string $col, string $originTable, string $originCol, string $as = '', string $key = '')
    {
        return $this->join($table, $col, $originTable, $originCol, $as, $key, 'LEFT JOIN');
    }

    /**
     * SQL JOIN (复杂连表)
     *
     * @param string $sql
     *
     * @return static
     */
    public function joinBySql(string $sql, $key = '')
    {
        if ($key === '') {
            $this->_join[] = $sql;
        } else {
            $this->_join[$key] = $sql;
        }

        return $this;
    }

    /**
     * Unset JOIN
     *
     * @param string $key
     *
     * @return static
     */
    public function unsetJoin(string $key)
    {
        unset($this->_join[$key]);

        return $this;
    }

    protected function buildJoin()
    {
        if (count($this->_join) === 0) {
            return '';
        }

        return ' ' . implode(' ', $this->_join);
    }

    /**
     * SQL ORDER_BY
     *
     * @param string $orderBy
     *
     * @return static
     */
    public function orderBy(string $orderBy = '')
    {
        if ($orderBy === '') {
            $this->_orderBy = '';
        } else {
            $this->_orderBy = ' ORDER BY ' . $orderBy;
        }

        return $this;
    }

    /**
     * SQL LIMIT and OFFSET
     *
     * @param $limit
     * @param $offset
     *
     * @return static
     */
    public function limit(int $limit = 0, int $offset = 0)
    {
        if ($limit === 0) {
            $this->_limit = '';
        } else {
            if ($offset === 0) {
                $this->_limit = ' LIMIT ' . $limit;
            } else {
                $this->_limit = ' LIMIT ' . $offset . ', ' . $limit;
            }
        }

        return $this;
    }

    /**
     * 绑定数据
     *
     * @param $value 数据
     *
     * @return string 用于SQL中的Key标记
     */
    protected function bind($value): string
    {
        $key                 = 'b_v_' . $this->_valuesCounter++;
        $this->_values[$key] = $value;

        return ':' . $key;
    }

    /**
     * 返回主库CONN
     *
     * @return Conn
     */
    protected function getMaster()
    {
        if ($this->_db === null) {
            throw new ErrorSys('SQL Error: DB is null');
        }

        return $this->_db->getMaster();
    }

    /**
     * 返回从库CONN
     *
     * @return Conn
     */
    protected function getSlave()
    {
        if ($this->_db === null) {
            throw new ErrorSys('SQL Error: DB is null');
        }

        return $this->_db->getSlave();
    }
}
