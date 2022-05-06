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

class Delete extends AbstractSql
{
    const SQL_TYPE = 'delete';

    public function __construct($table)
    {
        $this->_table = $table;
    }

    protected function build(): string
    {
        $this->_sql = 'DELETE FROM ' . $this->_table . $this->buildWhere() . $this->_orderBy . $this->_limit;

        return $this->_sql;
    }
}
