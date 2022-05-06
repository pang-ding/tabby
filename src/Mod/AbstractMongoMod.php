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

abstract class AbstractMongoMod
{
    const SEQ_COLLECTION = 'tabby_sequence';

    /**
     * 集合
     *
     * @var string
     */
    protected static $_TABLE_NAME;

    /**
     * Collections
     *
     * @var array
     */
    private static $_Collections = [];

    /**
     * 是否使用 ObjectId
     *
     * @var bool
     */
    protected static $_OBJECT_ID = true;

    /**
     * 主键
     *
     * @var string
     */
    public static $_TABLE_ID = '_id';

    /**
     * SESSION
     *
     * @var \MongoDB\Driver\Session
     */
    private static $_SESSION = null;

    /**
     * 返回集合名
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return static::$_TABLE_NAME;
    }

    /**
     * Collection
     *
     * @return \MongoDB\Collection
     */
    public static function getCollection()
    {
        $table = static::getTableName();
        if (!isset(static::$_Collections[$table])) {
            static::$_Collections[$table] = \Tabby\Tabby::$DI::Mongo()->$table;
        }

        return static::$_Collections[$table];
    }

    /**
     * 开启事务
     *
     * @param bool $causalConsistency 因果一致, 保证分布式环境下能读到之前写的内容, 默认 false, 如果后续操作对前置数据有依赖, 则需要开启
     *
     */
    public static function startTransaction($causalConsistency = false): void
    {
        $manager = static::getCollection()->getManager();

        self::$_SESSION = $manager->startSession(['causalConsistency' => $causalConsistency]);
        self::$_SESSION->startTransaction();
    }

    /**
     * 提交事务
     */
    public static function commitTransaction(): void
    {
        if (self::$_SESSION !== null) {
            self::$_SESSION->commitTransaction();
            self::$_SESSION = null;
        }
    }

    /**
     * 回滚事务
     */
    public static function abortTransaction(): void
    {
        if (self::$_SESSION !== null) {
            self::$_SESSION->abortTransaction();
            self::$_SESSION = null;
        }
    }

    /**
     * 根据 ID 获取数据
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public static function getById($id, $projection = [])
    {
        $options['projection'] = $projection;

        return static::findOne([static::$_TABLE_ID => static::formatId($id)], $options);
    }

    /**
     * 判断 ID 是否存在
     *
     * @param mixed $id
     *
     * @return bool
     */
    public static function hasId($id): bool
    {
        return !empty(static::getById($id, ['_id' => 1]));
    }

    /**
     * 取得返回值
     *
     * @param string $col
     * @param array  $filter
     *
     * @return mixed
     */
    public static function getValue($col, $filter = [], $options = [])
    {
        $options['projection'] = [
            '_id' => -1,
            $col  => 1,
        ];
        $rst = static::getCollection()->findOne($filter, $options);
        if (empty($rst)) {
            return null;
        }

        return $rst[$col] ?? null;
    }

    /**
     * 插入数据
     *
     * @param mixed $data
     *
     * @return mixed _id
     */
    public static function insert($data, $options = [])
    {
        $rst = static::insertOne($data, $options);

        return $rst->getInsertedId();
    }

    /**
     * 根据 ID 更新数据
     *
     * @param int   $id
     * @param mixed $data
     *
     * @return int
     */
    public static function updateById($id, $data, $options = [])
    {
        $rst = static::updateOne([static::$_TABLE_ID => static::formatId($id)], ['$set' => $data], $options);

        return $rst->getModifiedCount();
    }

    /**
     * 更新数据
     *
     * @param array $filter
     * @param mixed $data
     *
     * @return int
     */
    public static function update($filter, $data, $options = [])
    {
        $rst = static::updateMany($filter, ['$set' => $data], $options);

        return $rst->getModifiedCount();
    }

    /**
     * 简单列表
     *
     * @param array $projection
     * @param array $filter
     *
     * @return \MongoDB\Cursor
     */
    public static function list(array $projection = [], $filter = [], $options = [])
    {
        $collection            = static::getCollection();
        $options['projection'] = $projection;
        $cursor                = $collection->find($filter, $options);

        return $cursor->toArray();
    }

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
    {
        $collection       = static::getCollection();
        $options          = array_merge($options, [
            'limit'      => $size,
            'skip'       => ($page - 1) * $size,
            'projection' => $projection
        ]);
        $cursor           = $collection->find($filter, $options);
        $count            = $collection->countDocuments($filter);

        return [TabbyConsts::MOD_PAGE_LIST_FIELD => $cursor->toArray(), TabbyConsts::MOD_PAGE_TOTAL_FIELD => $count];
    }

    /**
     * 自增ID
     *
     *
     * @return mixed
     */
    public static function sequence()
    {
        $command = [
            'findAndModify'=> static::SEQ_COLLECTION,
            'update'       => [
                '$inc' => ['id' => 1]
            ],
            'query' => [
                'key'=> static::getTableName()
            ],
            'new'   => true,
            'upsert'=> true
        ];

        $rst = \Tabby\Tabby::$DI::Mongo()->command($command)->toArray();

        return $rst[0]['value']['id'];
    }

    /**
     * Executes an aggregation framework pipeline on the collection.
     *
     * Note: this method's return value depends on the MongoDB server version
     * and the "useCursor" option. If "useCursor" is true, a Cursor will be
     * returned; otherwise, an ArrayIterator is returned, which wraps the
     * "result" array from the command response document.
     *
     * @see Aggregate::__construct() for supported options
     *
     * @param array $pipeline List of pipeline operations
     * @param array $options  Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return Traversable
     */
    public static function aggregate(array $pipeline, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->aggregate($pipeline, $options);
    }

    /**
     * Gets the number of documents matching the filter.
     *
     * @see CountDocuments::__construct() for supported options
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array        $options Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return int
     */
    public static function countDocuments($filter = [], array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->countDocuments($filter, $options);
    }

    /**
     * Deletes all documents matching the filter.
     *
     * @see DeleteMany::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/delete/
     *
     * @param array|object $filter  Query by which to delete documents
     * @param array        $options Command options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return DeleteResult
     */
    public static function deleteMany($filter, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->deleteMany($filter, $options);
    }

    /**
     * Deletes at most one document matching the filter.
     *
     * @see DeleteOne::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/delete/
     *
     * @param array|object $filter  Query by which to delete documents
     * @param array        $options Command options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return DeleteResult
     */
    public static function deleteOne($filter, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->deleteOne($filter, $options);
    }

    /**
     * Finds the distinct values for a specified field across the collection.
     *
     * @see Distinct::__construct() for supported options
     *
     * @param string       $fieldName Field for which to return distinct values
     * @param array|object $filter    Query by which to filter documents
     * @param array        $options   Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return mixed[]
     */
    public static function distinct($fieldName, $filter = [], array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->distinct($fieldName, $filter, $options);
    }

    /**
     * Gets an estimated number of documents in the collection using the collection metadata.
     *
     * @see EstimatedDocumentCount::__construct() for supported options
     *
     * @param array $options Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return int
     */
    public static function estimatedDocumentCount(array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->estimatedDocumentCount($options);
    }

    /**
     * Finds documents matching the query.
     *
     * @see Find::__construct() for supported options
     * @see http://docs.mongodb.org/manual/core/read-operations-introduction/
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array        $options Additional options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return Cursor
     */
    public static function find($filter = [], array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->find($filter, $options);
    }

    /**
     * Finds a single document matching the query.
     *
     * @see FindOne::__construct() for supported options
     * @see http://docs.mongodb.org/manual/core/read-operations-introduction/
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array        $options Additional options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return array|object|null
     */
    public static function findOne($filter = [], array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->findOne($filter, $options);
    }

    /**
     * Finds a single document and deletes it, returning the original.
     *
     * The document to return may be null if no document matched the filter.
     *
     * @see FindOneAndDelete::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array        $options Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return array|object|null
     */
    public static function findOneAndDelete($filter, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->findOneAndDelete($filter, $options);
    }

    /**
     * Finds a single document and replaces it, returning either the original or
     * the replaced document.
     *
     * The document to return may be null if no document matched the filter. By
     * default, the original document is returned. Specify
     * FindOneAndReplace::RETURN_DOCUMENT_AFTER for the "returnDocument" option
     * to return the updated document.
     *
     * @see FindOneAndReplace::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     *
     * @param array|object $filter      Query by which to filter documents
     * @param array|object $replacement Replacement document
     * @param array        $options     Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return array|object|null
     */
    public static function findOneAndReplace($filter, $replacement, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * Finds a single document and updates it, returning either the original or
     * the updated document.
     *
     * The document to return may be null if no document matched the filter. By
     * default, the original document is returned. Specify
     * FindOneAndUpdate::RETURN_DOCUMENT_AFTER for the "returnDocument" option
     * to return the updated document.
     *
     * @see FindOneAndReplace::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array|object $update  Update to apply to the matched document
     * @param array        $options Command options
     *
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return array|object|null
     */
    public static function findOneAndUpdate($filter, $update, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->findOneAndUpdate($filter, $update, $options);
    }

    /**
     * Inserts multiple documents.
     *
     * @see InsertMany::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/insert/
     *
     * @param array[]|object[] $documents The documents to insert
     * @param array            $options   Command options
     *
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return InsertManyResult
     */
    public static function insertMany(array $documents, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->insertMany($documents, $options);
    }

    /**
     * Inserts one document.
     *
     * @see InsertOne::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/insert/
     *
     * @param array|object $document The document to insert
     * @param array        $options  Command options
     *
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return InsertOneResult
     */
    public static function insertOne($document, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->insertOne($document, $options);
    }

    /**
     * Replaces at most one document matching the filter.
     *
     * @see ReplaceOne::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/update/
     *
     * @param array|object $filter      Query by which to filter documents
     * @param array|object $replacement Replacement document
     * @param array        $options     Command options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return UpdateResult
     */
    public static function replaceOne($filter, $replacement, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->replaceOne($filter, $replacement, $options);
    }

    /**
     * Updates all documents matching the filter.
     *
     * @see UpdateMany::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/update/
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array|object $update  Update to apply to the matched documents
     * @param array        $options Command options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return UpdateResult
     */
    public static function updateMany($filter, $update, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->updateMany($filter, $update, $options);
    }

    /**
     * Updates at most one document matching the filter.
     *
     * @see UpdateOne::__construct() for supported options
     * @see http://docs.mongodb.org/manual/reference/command/update/
     *
     * @param array|object $filter  Query by which to filter documents
     * @param array|object $update  Update to apply to the matched document
     * @param array        $options Command options
     *
     * @throws UnsupportedException     if options are not supported by the selected server
     * @throws InvalidArgumentException for parameter/option parsing errors
     * @throws DriverRuntimeException   for other driver errors (e.g. connection errors)
     *
     * @return UpdateResult
     */
    public static function updateOne($filter, $update, array $options = [])
    {
        static::initOptions($options);

        return static::getCollection()->updateOne($filter, $update, $options);
    }

    /**
     * formatId
     *
     * @param mixed $id
     *
     * @return mixed
     */
    protected static function formatId($id)
    {
        if (!static::$_OBJECT_ID || $id instanceof \MongoDB\BSON\ObjectId) {
            return $id;
        }

        try {
            return new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            throw new \ErrorData("Mongodb: FormatId failed: '{$id}'");
        }
    }

    protected static function initOptions(array &$options): void
    {
        if (self::$_SESSION !== null) {
            $options['session'] = self::$_SESSION;
        }
    }
}
