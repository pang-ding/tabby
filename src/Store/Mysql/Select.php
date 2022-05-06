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

class Select extends Sql
{
    const DEFAULT_PAGE_SIZE = 20;
    const SQL_TYPE          = 'select';

    protected $_select    = '';
    protected $_from      = '';
    protected $_groupBy   = '';
    protected $_having    = '';
    protected $_forUpdate = '';

    public function __construct($select = '*')
    {
        $this->select($select);
    }

    /**
     * SQL SELECT
     *
     * @param mixed $select
     *
     * @return static
     */
    public function select($select)
    {
        if (is_string($select)) {
            $this->_select = 'SELECT ' . $select;

            return $this;
        }
        $items = [];
        foreach ($select as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $c) {
                    $items[] = $k . '.' . $c;
                }
            } else {
                $items[] = $v;
            }
        }
        $this->_select = 'SELECT ' . implode(', ', $items);

        return $this;
    }

    /**
     * SQL FROM
     *
     * @param string $table
     * @param string $tableAs
     *
     * @return static
     */
    public function from(string $table, string $alias = '')
    {
        $this->_table = $table;
        $this->_from  = ' FROM ' . $table;
        if ($alias !== '') {
            $this->_from .= ' AS ' . $alias;
        }

        return $this;
    }

    /**
     * SQL GROUP_BY
     *
     * @param string $groupBy
     * @param mixed  $having
     *
     * @return static
     */
    public function groupBy(string $groupBy = '')
    {
        if ($groupBy === '') {
            $this->_groupBy = '';
        } else {
            $this->_groupBy = ' GROUP BY ' . $groupBy;
        }

        return $this;
    }

    /**
     * SQL HAVING
     *
     * @param mixed   $where
     * @param Boolean $isOr
     *
     * @return static
     */
    public function having($where, bool $isOr = false)
    {
        $sqlHaving = $this->formatWhere($where, $isOr);
        if ($sqlHaving === '') {
            $this->_having = '';
        } else {
            $this->_having = ' HAVING ' . $sqlHaving;
        }

        return $this;
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
        $this->_forUpdate = $forUpdate;
        $this->_forUpdate = $forUpdate ? ' FOR UPDATE' : '';

        return $this;
    }

    /**
     * 取得count数量 (忽略 select 与 limit 设置)
     *
     * @return int
     */
    public function getCount(): int
    {
        $_tempSelect   = $this->_select;
        $_tempLimit    = $this->_limit;
        $this->_select = 'SELECT count(1)';
        $this->_limit  = '';
        $total         = $this->fetchValue();
        $this->_select = $_tempSelect;
        $this->_limit  = $_tempLimit;

        return (int) $total;
    }

    /**
     * 分页
     *
     * @param int $size    每页条目
     * @param int &$total  总条目(引用), 执行过程赋值
     * @param int $pageNum 当前页数
     *
     * @return array
     */
    public function getPageData(int $size, int &$total, int $pageNum = 1): array
    {
        if ($size < 1) {
            throw new ErrorSys('Sql Error: getPageData size < 1');
        }
        if ($pageNum < 1) {
            $pageNum = 1;
        }
        $_tempSelect   = $this->_select;
        $this->_select = 'SELECT count(1)';
        $total         = (int) $this->limit()->fetchValue();
        $this->_select = $_tempSelect;

        $offset = ($pageNum - 1) * $size;
        if ($offset > $total) {
            $offset = $total;
        }
        $list = $this->limit($size, $offset)->fetchAll();

        return $list;
    }

    protected function build(): string
    {
        $this->_sql = $this->_select . $this->_from . $this->buildJoin() . $this->buildWhere(true)
        . $this->_groupBy . $this->_having . $this->_orderBy . $this->_limit . $this->_forUpdate;

        return $this->_sql;
    }
}
