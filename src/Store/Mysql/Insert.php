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

class Insert extends AbstractSql
{
    const SQL_TYPE = 'insert';

    public function __construct($table)
    {
        $this->_table = $table;
    }

    /**
     * 执行 Sql 语句, 返回 LastID
     *
     * @param mixed $values
     *
     * @return string|false LastID
     */
    public function exec($values = [])
    {
        if ($values !== null) {
            $this->bindValues($values);
        }
        $conn = $this->getMaster();
        $conn->execute($this->build(), $this->_values);

        return $conn->lastInsertId();
    }

    protected function build(): string
    {
        $this->_sql = 'INSERT INTO ' . $this->_table . $this->_set;

        return $this->_sql;
    }
}
