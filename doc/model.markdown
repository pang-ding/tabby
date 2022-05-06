# Model & Mysql

* DB实例
* Sql Builder
* Model

## DB实例

通过 ```\Tabby\Store\Mysql\DB``` 创建实例 内部实现了lazy, 不需要再做单例

___如果启用主从分离, 只需要传入 $slave(Conn) 参数即可___

```php
// \Tabby\Store\Mysql\DB
// \Tabby\Store\Mysql\Conn
public function __construct(Conn $master, ?Conn $slave = null)

// 将实例放入 DI, 后续通过 \T::$DI::DB() 得到实例
\T::$DI[DiConsts::DI_MYSQL] = new DB(
    new Conn(
        'mysql:host=' . \T::getConf('mysql.host') . ';port=' . \T::getConf('mysql.port') . ';dbname=' . \T::getConf('mysql.dbname') . ';charset=utf8mb4;',
        \T::getConf('mysql.username'),
        \T::getConf('mysql.password'),
        [
            \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT => true,
        ]
    )
);
```

## Model

```php
class FooMod extends \Tabby\Mod\AbstractMod
{
    // 表名
    protected static $_TABLE_NAME = 'foo';
}

// 返回 Select 对象 (Sql Builder)
Mod::select($select = '*') 

// 返回ID对应的记录
Mod::getById(int $id, $select = '*') 

// 根据WHERE条件，取得返回值
Mod::fetchValue($where, string $select) // $select 只能写字段名

//根据WHERE条件，取得一行记录
Mod::fetchRow($where, $select = '*')

//根据WHERE条件，取得记录集
Mod::fetchAll($where, $select = '*')

/**
 * 得到KV字典
 * dict('class_key', 'class_name', ['enable'=>1], 'class_key desc')
 * return:[
 *   'class1' => 'CLASS_ONE',
 *   'class2' => 'CLASS_TWO',
 * ]
 */
Mod::dict(string $key, string $value, $where = '', string $order = '')

// 指定字段是否存在目标值
Mod::hasVal(string $col, $val, $where = ''): bool

/**
 * 列表
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
 */
Mod::list($select, $where, string $order = '', int $limit = 0, int $offset = 0, array $join=[])

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
Mod::pageList(int $size, int $pageNum, $select, $where, $order = '', $join=[])

Mod::insert($data)

Mod::update($where, $data, int $limit = 0)

Mod::updateById(int $id, $data)

Mod::delete($where, int $limit = 1)

Mod::deleteById(int $id)

Mod::hasId(int $id): bool

Mod::count($where): int

Mod::exist($where): bool
```

## Sql Builder

#### 得到数据库实例

```php
$db = \T::$DI::DB();
```

#### Select

```php
$select = $db->select()
    ->from()
    ->join()
    ->leftJoin()
    ->where()
    ->groupBy()
    ->having()
    ->orderBy()
    ->limit()
    ->forUpdate();

// 执行 Sql 取结果, 
$select->fetchAll(array $values) // 数组形式, 返回结果集

$select->fetchRow(array $values) // 返回第一行结果

$select->fetchValue(array $values) // 返回第一行第一列的值

$select->yield(array $values) // 返回一个迭代器, 数据量很大时使用

$select->getCount() // 返回count(1)数量 (忽略 select 与 limit 设置)

/* 分页列表
 * @param int $size 每页行数
 * @param int &$total 引用, 执行后赋值符合条件的总行数
 * @param int $pageNum 页数(从1开始)
 * 
 * @return 指定页结果集(fetchAll)
 */
$select->getPageData(int $size, int &$total, int $pageNum = 1) 


/** 参数 **/
select('*'); // SELECT *

select(['`name`', '`age`', '`level`']); // SELECT `name`, `age`, `level`

select('count(1) as `ct`'); // SELECT count(1) as `ct`

// SELECT `user`.`name`, `user`.`age`, `user`.`level`, `group`.`group_id`
select(['`user`' => ['`name`', '`age`', '`level`'], '`group`' => ['`group_id`']]); 


from('table'); // FROM table

from('table', 'tb'); // FROM table AS tb

/* JOIN */

// join可以多次设置
join('t1', 'c1', 't2', 'c2'); // JOIN t1 ON t2.c2 = t1.c1

join('t1', 'c1', 't2', 'c2', 'as_name'); // JOIN t1 AS as_name ON t2.c2 = as_name.c1

join('t1', 'c1', 't2', 'c2', 'as_name', 'key'); // key标志: 后续可以用$sql->unsetJoin('key')从$sql对象中删除这条JOIN

join('t1', 'c1', 't2', 'c2', 'as_name', '', 'LEFT JOIN'); // LEFT JOIN t1 AS as_name ON t2.c2 = as_name.c1

leftJoin('t1', 'c1', 't2', 'c2', 'as_name'); // 和上面一条效果一样

/* WHERE */

// 绑定变量名, 后续执行时赋值
// WHERE user_id > :uid AND group_id >= :gid
$sql->where([             
    'user_id'  => 'uid', 
    'group_id' => 'gid', 
])
// 执行时需要赋值, 否则Pdo抛异常
$sql->fetchAll(['uid'=>1,'gid'=>2]);

// 实际结果: WHERE user_id=:b_v_1 AND group_id=:b_v_2, 为便于理解直接写字面量, 后续都照此处理
// WHERE user_id=1 AND group_id=2
where([             
    'user_id'  => 1, 
    'group_id' => 2, 
])

// WHERE user_id > 1 AND group_id >= 2
where([             
    'user_id|>'   => 1, 
    'group_id|>=' => 2, 
])

// WHERE user_id in (1,2,3) AND user_name like 'search%'
where([             
    'user_id|in'     => [1, 2, 3], 
    'user_name|like' => 'search%', 
])

// 第二个参数设置 true, 直接 OR 处理(默认false: AND)
// WHERE user_id=1 OR group_id=2
where([             
    'user_id'  => 1, 
    'group_id' => 2, 
], true)

// 嵌套数组, 内层也会做 OR 处理 (多层嵌套会轮换 AND 和 OR)
// WHERE (c1 = 1 OR c2 = 2) AND (c3 = 3 OR c4 = 4)
where([
    ['c1' => 1, 'c2' => 2], 
    ['c3' => 3, 'c4' => 4]
])

// 字符串直接使用
where('user_id=1 AND group_id=2'); // WHERE user_id=1 AND group_id=2


// 第三个参数 $key ,后续可以用$sql->unsetWhere('key')从$sql对象中删除这条WHERE
$sql->where('user_id=1', false, 'where_key');
$sql->unsetWhere('where_key');


groupBy('`user`, age'); // GROUP BY `user`, age


having('count(`user`) > 1'); // HAVING count(`user`) > 1


orderBy('`user` DESC, age'); // ORDER BY `user` DESC, age


limit(10); // LIMIT 10

// 需要注意, LIMIT参数顺序和Sql语法相反. 必须吐槽一下Mysql LIMIT的语法设定, 太反直觉了... 
limit(10, 100); // LIMIT 100, 10


forUpdate() // FOR UPDATE, 同时影响DB对象执行时的主从选择(设置了从库, 启用主从分离时)


```

#### Insert

```php
// 返回LastID
$result = $db->insert('`group`')
             ->set(['`name`' => 'A'])
             ->exec();
```

#### Update

```php
// 返回受影响行数
$result = $db->update('`group`')
             ->set(['`name`' => 'A'])
             ->where(['id' => $id]) // where参数见 select
             ->limit(1)
             ->exec();
```

#### Delete

```php
// 返回受影响行数
$result = $db->delete('`group`')
             ->where(['id' => $id]) // where参数见 select
             ->limit(1)
             ->exec();
```

