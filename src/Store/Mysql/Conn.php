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

use PDO;
use Tabby\Error\ErrorSys;

class Conn
{
    /**
     * PDO
     *
     * @var \PDO
     */
    protected $_pdo = null;
    protected $_pdoArgs;
    protected $_username;
    protected $_passwd;
    protected $_dsn;

    /**
     * 创建一个表示数据库连接的实例(包装PDO)
     *
     * @param string $dsn      数据源名称或叫做 DSN，包含了请求连接到数据库的信息
     * @param string $username DSN字符串中的用户名。对于某些PDO驱动，此参数为可选项
     * @param string $passwd   DSN字符串中的密码。对于某些PDO驱动，此参数为可选项
     * @param array  $options  一个具体驱动的连接选项的键=>值数组
     * @param array  $sqls     创建PDO后要执行的SQL语句
     *
     * [
     *      PDO::ATTR_AUTOCOMMIT    => 1|0      自动commit每条SQL 默认:1
     *      PDO::ATTR_TIMEOUT       => int             超时(秒)
     *      PDO::ATTR_PERSISTENT    => true|false      持久连接
     *      PDO::ATTR_ERRMODE       => PDO::ERRMODE_SILENT|PDO::ERRMODE_WARNING|PDO::ERRMODE_EXCEPTION
     *      PDO::ATTR_ORACLE_NULLS  => PDO::NULL_NATURAL|PDO::NULL_EMPTY_STRING|PDO::NULL_TO_STRING
     * ]
     *
     * @return self
     */
    public function __construct(string $dsn, string $username = '', string $passwd = '', array $options = [], array $sqls = [])
    {
        $this->_pdoArgs = [
            'dsn'      => $dsn,
            'username' => $username,
            'password' => $passwd,
            'options'  => $options,
            'sqls'     => $sqls,
        ];
    }

    /**
     * 保持连接
     *
     */
    public function ping(): void
    {
        if ($this->_pdo === null) {
            $this->reConnect();

            return;
        }

        try {
            $this->_pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
        } catch (\Exception $e) {
            $this->reConnect();
        }
    }

    /**
     * 初始化连接, lazy
     *
     */
    public function connect(): void
    {
        if ($this->_pdo) {
            return;
        }

        $this->reConnect();
    }

    /**
     * 实例化PDO (连接数据库)
     *
     */
    public function reConnect(): void
    {
        try {
            $this->_pdo = new \PDO(
                $this->_pdoArgs['dsn'],
                $this->_pdoArgs['username'],
                $this->_pdoArgs['password'],
                $this->_pdoArgs['options']
            );
            foreach ($this->_pdoArgs['sqls'] as $sql) {
                $this->exec($sql);
            }
        } catch (\PDOException $exception) {
            throw new ErrorSys('MySQL' . $exception->getMessage());
        }
    }

    /**
     * 取回一个数据库连接的属性
     *
     * @param int $attribute PDO::ATTR_* 常量中的一个
     *
     * @return mixed 成功调用则返回请求的 PDO 属性值。不成功则返回 null
     */
    public function getAttribute(int $attribute)
    {
        $this->connect();

        return $this->_pdo->getAttribute($attribute);
    }

    /**
     * 设置个数据库连接的属性
     *
     * @param int   $attribute PDO::ATTR_* 常量中的一个
     * @param mixed $value     要设置的属性值
     *
     * @return bool 成功时返回 TRUE，或者在失败时返回 FALSE
     */
    public function setAttribute(int $attribute, $value): bool
    {
        $this->connect();

        return $this->_pdo->setAttribute($attribute, $value);
    }

    /**
     * 启动一个事务
     *
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
     */
    public function begin(): bool
    {
        $this->connect();

        return $this->_pdo->beginTransaction();
    }

    /**
     * 提交一个事务
     *
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE。
     */
    public function commit(): bool
    {
        $this->connect();

        return $this->_pdo->commit();
    }

    /**
     * 回滚一个事务
     *
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
     */
    public function rollBack(): bool
    {
        $this->connect();

        return $this->_pdo->rollBack();
    }

    /**
     * 检查是否在一个事务内
     *
     * @return bool 如果当前事务处于激活状态，则返回 TRUE ，否则返回 FALSE
     */
    public function inTransaction(): bool
    {
        $this->connect();

        return $this->_pdo->inTransaction();
    }

    /**
     * 返回最后插入行的ID或序列值
     * 不允许name参数
     *
     * @return string|false
     */
    public function lastInsertId()
    {
        $this->connect();

        return $this->_pdo->lastInsertId();
    }

    /**
     * 执行一条 SQL 语句，并返回受影响的行数
     *
     * @param string $sql 要被预处理和执行的 SQL 语句
     *
     * @return int|false 返回受修改或删除 SQL 语句影响的行数。如果没有受影响的行，则 PDO::exec() 返回 0 失败返回: false
     */
    public function exec(string $sql)
    {
        $this->connect();

        return $this->_pdo->exec($sql);
    }

    /**
     * 准备要执行的语句，并返回语句对象
     *
     * @param string $sql     必须是对目标数据库服务器有效的 SQL 语句模板
     * @param array  $options 数组包含一个或多个 key=>value 键值对，为返回的 PDOStatement 对象设置属性
     *
     * @return \PDOStatement 如果数据库服务器完成准备了语句， PDO::prepare() 返回 PDOStatement 对象。
     *                       如果数据库服务器无法准备语句， PDO::prepare() 返回 FALSE 或抛出 PDOException (取决于 错误处理器)
     */
    public function prepare(string $sql, array $options = []): \PDOStatement
    {
        $this->connect();

        return $this->_pdo->prepare($sql, $options);
    }

    /**
     * 返回 PDO 对象
     *
     * @return ?\PDO
     */
    public function getPdo(): ?\PDO
    {
        $this->connect();

        return $this->_pdo;
    }

    /**
     * 执行 Sql 语句
     *
     * @param string $sql
     * @param array  $values
     *
     * @return int 受影响行数
     */
    public function execute(string $sql, array $values = []): int
    {
        $this->connect();
        $stmt = $this->initStmt($sql, $values);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * 执行 Sql 语句, 返回结果集
     *
     * @param string $sql
     * @param array  $values
     *
     * @return array|false
     */
    public function fetchAll(string $sql, array $values = [])
    {
        $this->connect();
        $stmt = $this->initStmt($sql, $values);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 执行 Sql 语句, 返回 Iterator
     *
     * @param string $sql
     * @param array  $values
     *
     * @return \Iterator
     */
    public function yield(string $sql, array $values = [])
    {
        $this->connect();
        $stmt = $this->initStmt($sql, $values);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * 执行 Sql 语句, 返回一行数据
     *
     * @param string $sql
     * @param array  $values
     *
     * @return array|false
     */
    public function fetchRow(string $sql, array $values = [])
    {
        $this->connect();
        $stmt = $this->initStmt($sql, $values);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 执行 Sql 语句, 返回数据
     *
     * @param string $sql
     * @param array  $values
     *
     * @return mixed
     */
    public function fetchValue(string $sql, array $values = [])
    {
        $this->connect();
        $stmt = $this->initStmt($sql, $values);
        $stmt->execute();

        return $stmt->fetchColumn(0);
    }

    protected function initStmt(string $sql, array $values = [], $options = []): \PDOStatement
    {
        $stmt = $this->_pdo->prepare($sql, $options);
        $this->bindValues($stmt, $values);

        return $stmt;
    }

    protected function bindValues($sth, $values = []): void
    {
        foreach ($values as $key => $value) {
            if (is_int($value)) {
                $sth->bindValue($key, $value, \PDO::PARAM_INT);
            } elseif (is_string($value)) {// TODO: 测试blob之类的情况, 判断要不要去掉string
                $sth->bindValue($key, $value, \PDO::PARAM_STR);
            } elseif (is_bool($value)) {
                $sth->bindValue($key, $value, \PDO::PARAM_BOOL);
            } elseif (null === $value) {
                $sth->bindValue($key, $value, \PDO::PARAM_NULL);
            } else {
                $sth->bindValue($key, $value);
            }
        }
    }

    // /**
    //  * 获取数据库句柄上一次操作相关的 SQLSTATE
    //  *
    //  * @return mixed
    //  */
    // public function errorCode()
    // {
    //     $this->connect();
    //     return $this->_pdo->errorCode();
    // }

    // /**
    //  * Fetch extended error information associated with the last operation on the database handle
    //  *
    //  * @return array
    //  */
    // public function errorInfo(): array
    // {
    //     $this->connect();
    //     return $this->_pdo->errorInfo();
    // }
}
