<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Mod;

use Consts\TabbyConsts;
use Tabby\Error\ErrorSys;
use Tabby\Framework\DI;
use Tabby\Store\Mysql\DB;
use Consts\DiConsts;

abstract class AbstractMod
{
    /**
     * DB 对象
     *
     * @var ?DB
     */
    protected static $_DB = null;

    /**
     * DI_KEY 连接多实例时使用
     * 默认DiConsts::DI_MYSQL
     *
     * @var string
     */
    protected static $_DI_KEY = DiConsts::DI_MYSQL;

    /**
     * 表名
     *
     * @var string
     */
    protected static $_TABLE_NAME;

    /**
     * 主键 (请勿重写)
     *
     * @var string
     */
    protected static $_TABLE_ID = 'id';

    /**
     * 返回表名
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return static::$_TABLE_NAME;
    }

    /**
     * 创建查询语句对象
     *
     * @param mixed $select
     *
     * @return Select
     */
    public static function select($select = '*')
    {
        return static::getDB()->select($select)->from(static::getTableName());
    }

    /**
     * 根据 ID 获取数据
     *
     * @param int   $id
     * @param mixed $select
     *
     * @return mixed
     */
    public static function getById(int $id, $select = '*')
    {
        return static::select($select)
            ->where([static::$_TABLE_ID => $id])
            ->fetchRow();
    }

    /**
     * 根据WHERE条件，取行数
     *
     * @param mixed $where
     *
     * @return mixed
     */
    public static function count($where)
    {
        return static::select('count(1)')
            ->where($where)
            ->fetchValue();
    }

    /**
     * 根据WHERE条件，判断是否存在
     *
     * @param mixed $where
     *
     * @return bool
     */
    public static function exist($where): bool
    {
        return static::count($where) > 0;
    }

    /**
     * 根据WHERE条件，取得返回值
     *
     * @param mixed  $select (这里只能写字段名)
     * @param string $where
     *
     * @return mixed
     */
    public static function fetchValue($where, string $select)
    {
        return static::select($select)
            ->where($where)
            ->fetchValue();
    }

    /**
     * 根据WHERE条件，取得一行记录
     *
     * @param mixed $where
     * @param mixed $select
     *
     * @return mixed
     */
    public static function fetchRow($where, $select = '*')
    {
        return static::select($select)
            ->where($where)
            ->fetchRow();
    }

    /**
     * 根据WHERE条件，取得记录集
     *
     * @param mixed $where
     * @param mixed $select
     *
     * @return mixed
     */
    public static function fetchAll($where, $select = '*')
    {
        return static::select($select)
            ->where($where)
            ->fetchAll();
    }

    /**
     * 词典
     *
     * @param string $key   字段名
     * @param string $value 字段名
     * @param mixed  $where
     * @param string $order orderBy
     *
     * @return array
     */
    public static function dict(string $key, string $value, $where = '', string $order = ''): array
    {
        $data = static::select("{$key}, {$value}")
            ->where($where)
            ->orderBy($order)
            ->fetchAll();
        $rst = [];
        if (is_array($data)) {
            foreach ($data as $v) {
                $rst[$v[$key]] = $v[$value];
            }
        }

        return $rst;
    }

    /**
     * 判断 ID 是否存在
     *
     * @param int $id
     *
     * @return bool
     */
    public static function hasId(int $id): bool
    {
        return static::select(static::$_TABLE_ID)
            ->where([static::$_TABLE_ID => $id])
            ->limit(1)
            ->fetchValue() > 0;
    }

    /**
     * 判断 指定字段是否存在目标值
     *
     * @param string $col   字段名
     * @param string $value 目标值
     * @param mixed  $where
     *
     * @return bool
     */
    public static function hasVal(string $col, $val, $where = ''): bool
    {
        if ($where === '') {
            $where = [];
        } elseif (!is_array($where)) {
            $where = [$where];
        }
        $where[$col] = $val;

        return static::select('count(1)')
            ->where($where)
            ->fetchValue() > 0;
    }

    /**
     * 简单列表
     *
     * @param mixed  $select
     * @param mixed  $where
     * @param string $order  orderBy
     * @param int    $limit
     * @param int    $offset
     * @param array  $join
     *
     * JOIN:
     *   - table:     关联表 默认值: 当前表: static::$_TABLE_NAME
     *   - col:       关联列
     *   - originTable: 原表
     *   - originCol: 原表关联列
     *   - as:        别名 默认值: '',不设置alias
     *   - type:      JOIN|LEFT JOIN|RIGHT JOIN 默认值: 'JOIN'
     *   - key:       后续操作中删除这条连接时使用 默认值: ''
     *
     * @return mixed
     */
    public static function list($select, $where, string $order = '', int $limit = 0, int $offset = 0, array $join=[])
    {
        $sql = static::select($select)
            ->where($where)
            ->orderBy($order)
            ->limit($limit, $offset);
        if (!empty($join)) {
            foreach ($join as $v) {
                $sql->join($v['table'], $v['col'], $v['originTable'] ?? static::$_TABLE_NAME, $v['originCol'], $v['as'] ?? '', '', $v['type'] ?? 'JOIN');
            }
        }

        return $sql->fetchAll();
    }

    /**
     * 分页列表
     *
     * @param int    $size    每页条数
     * @param int    $pageNum 当前页码
     * @param mixed  $select
     * @param mixed  $where
     * @param string $order   orderBy
     * @param array  $join
     *
     * JOIN:
     *   - table:     关联表 默认值: 当前表: static::$_TABLE_NAME
     *   - col:       关联列
     *   - originTable: 原表
     *   - originCol: 原表关联列
     *   - as:        别名 默认值: '',不设置alias
     *   - type:      JOIN|LEFT JOIN|RIGHT JOIN 默认值: 'JOIN'
     *   - key:       后续操作中删除这条连接时使用 默认值: ''
     *
     * @return array [TabbyConsts::MOD_PAGE_LIST_FIELD => {当前页数据}, TabbyConsts::MOD_PAGE_TOTAL_FIELD => {总行数}]
     */
    public static function pageList(int $size, int $pageNum, $select, $where, $order = '', $join=[])
    {
        $total = 0;
        $sql   = static::select($select)
            ->where($where)
            ->orderBy($order);
        if (!empty($join)) {
            foreach ($join as $v) {
                $sql->join($v['table'], $v['col'], $v['originTable'] ?? static::$_TABLE_NAME, $v['originCol'], $v['as'] ?? '', '', $v['type'] ?? 'JOIN');
            }
        }
        $list = $sql->getPageData($size, $total, $pageNum);

        return [TabbyConsts::MOD_PAGE_LIST_FIELD => $list, TabbyConsts::MOD_PAGE_TOTAL_FIELD => $total];
    }

    /**
     * 插入数据
     *
     * @param mixed $data
     *
     * @return string|false LastID
     */
    public static function insert($data)
    {
        $sql = static::getDB()
            ->insert(static::getTableName())
            ->set($data);

        return $sql->exec();
    }

    /**
     * 根据 WHERE条件 更新数据
     *
     * @param mixed $where
     * @param mixed $data
     * @param int   $limit 默认0
     *
     * @return int
     */
    public static function update($where, $data, int $limit = 0)
    {
        $sql = static::getDB()
            ->update(static::getTableName())
            ->set($data)
            ->where($where);
        if ($limit > 0) {
            $sql->limit($limit);
        }

        return $sql->exec();
    }

    /**
     * 根据 ID 更新数据
     *
     * @param int   $id
     * @param mixed $data
     *
     * @return int
     */
    public static function updateById(int $id, $data)
    {
        if ($id < 1) {
            throw new ErrorSys('Mod Error: ' . static::class . '::updateById(' . $id . ') id < 1');
        }

        return static::update([static::$_TABLE_ID => $id], $data, 1);
    }

    /**
     * 删除数据
     *
     * @param mixed $where
     * @param int   $limit 默认1
     *
     * @return int
     */
    public static function delete($where, int $limit = 1)
    {
        $sql = static::getDB()
            ->delete(static::getTableName())
            ->where($where);
        if ($limit > 0) {
            $sql->limit($limit);
        }

        return $sql->exec();
    }

    /**
     * 根据 ID 删除数据
     *
     * @param int $id
     *
     * @return int
     */
    public static function deleteById(int $id)
    {
        if ($id < 1) {
            throw new ErrorSys('Mod Error: ' . static::class . '::deleteById(' . $id . ') id < 1');
        }

        return static::delete([static::$_TABLE_ID => $id], 1);
    }

    /**
     * 返回 DB 对象
     *
     * @return DB
     */
    protected static function getDB(): DB
    {
        if (static::$_DB === null) {
            static::$_DB = DI::getIns()->get(static::$_DI_KEY);
        }

        return static::$_DB;
    }
}
