# mongo

AbstractMongoMod

### 注意:

* ObjectId 没做处理, 用的时候转一下 
* 和Mysql不同, 验证器输出的Data不能直接用, 需要 toArray()
* 事务需要在复制集环境下使用

使用方式详见:

<https://www.mongodb.com/docs/php-library/master/>

#### 默认没在composer.json里加引用, 使用的时候自己加一下

```json
"mongodb/mongodb": "^1.8"
```

#### 连接

```php
// Bootstrap中

\T::$DI['mongo'] = function () {
    try {
        $mongoConf = \T::$Conf['mongo'];
        $client    = new \MongoDB\Client($mongoConf['dsn']);
        // $client    = new \MongoDB\Client("mongodb://{$mongoConf['host']}:{$mongoConf['port']}");
        $db        = $mongoConf['dbname'];

        return $client->$db;
    } catch (Exception $e) {
        throw new ErrorSys('MongoDB 连接失败:' . $e->getMessage());

        return;
    }
};
```

#### Model方法

```php
// 返回当前Model对应的自增ID
sequence()

/**
* 返回集合名
*
* @return string
*/
public static function getTableName(): string

/**
* Collection
*
* @return \MongoDB\Collection
*/
public static function getCollection()

/**
* 开启事务
*
* @param bool $causalConsistency 因果一致, 保证分布式环境下能读到之前写的内容, 默认 false, 如果后续操作对前置数据有依赖, 则需要开启
*
*/
public static function startTransaction($causalConsistency = false): void

/**
* 提交事务
*/
public static function commitTransaction(): 

/**
* 回滚事务
*/
public static function abortTransaction(): void

/**
* 判断 ID 是否存在
*
* @param mixed $id
*
* @return bool
*/
public static function hasId($id): bool

/**
* 根据 ID 获取数据
*
* @param mixed $id
*
* @return mixed
*/
public static function getById($id, $projection = [])

/**
* 取得返回值
*
* @param string $col
* @param array  $filter
*
* @return mixed
*/
public static function getValue($col, $filter = [], $options = [])

/**
* 插入数据
*
* @param mixed $data
*
* @return mixed _id
*/
public static function insert($data, $options = [])

/**
* 根据 ID 更新数据
*
* @param int   $id
* @param mixed $data
*
* @return int
*/
public static function updateById($id, $data, $options = [])

/**
* 更新数据
*
* @param array $filter
* @param mixed $data
*
* @return int
*/
public static function update($filter, $data, $options = [])

/**
 * 分页列表
 *
 * @param int   $size
 * @param int   $page
 * @param array $projection
 * @param array $filter
 * @param array $options
 *
 * @return mixed
 */
public static function pageList(int $size, int $page = 1, array $projection = [], array $filter = [], $options = [])

```