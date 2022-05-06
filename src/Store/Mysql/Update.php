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

class Update extends AbstractSql
{
    const SQL_TYPE = 'update';

    public function __construct($table)
    {
        $this->_table = $table;
    }

    protected function build(): string
    {
        //暂不支持JOIN
        $this->_sql = 'UPDATE ' . $this->_table . $this->_set . $this->buildWhere(true) . $this->_orderBy . $this->_limit;

        return $this->_sql;
    }
}
