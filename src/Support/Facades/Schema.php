<?php

namespace ORPTech\MigrationPartition\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use RuntimeException;

/**
 * @method static  createRangePartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $rangeKey)
 * @method static  createRangePartition(string $table, \Closure $callback, string $subfixForPartition, string $startDate, string $endDate)
 * @method static  attachRangePartition(string $table, \Closure $callback, string $partitionTableName, string $startDate, string $endDate)
 * @method static  detachPartition(string $table, \Closure $callback, string $partitionTableName)
 * @method static  createListPartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $listPartitionKey)
 * @method static  createListPartition(string $table, \Closure $callback, string $subfixForPartition, string $listPartitionValue)
 * @method static  attachListPartition(string $table, \Closure $callback, string $partitionTableName, string $listPartitionValue)
 * @method static  createHashPartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $hashPartitionKey)
 * @method static  createHashPartition(string $table, \Closure $callback, string $subfixForPartition, int $hashModulus, int $hashRemainder)
 * @method static  attachHashPartition(string $table, \Closure $callback, string $partitionTableName, int $hashModulus, int $hashRemainder)
 * @method static  getAllRangePartitionedTables()
 * @method static  create(string $table, \Closure $callback)
 * @method static  createcreateDatabase(string $name)
 * @method static  createdisableForeignKeyConstraints()
 * @method static  createdrop(string $table)
 * @method static  createdropDatabaseIfExists(string $name)
 * @method static  createdropIfExists(string $table)
 * @method static  createenableForeignKeyConstraints()
 * @method static  createrename(string $from, string $to)
 * @method static  createtable(string $table, \Closure $callback)
 * @method static  bool hasColumn(string $table, string $column)
 * @method static  bool hasColumns(string $table, array $columns)
 * @method static  bool dropColumns(string $table, array $columns)
 * @method static  void whenTableHasColumn(string $table, string $column, \Closure $callback)
 * @method static  void whenTableDoesntHaveColumn(string $table, string $column, \Closure $callback)
 * @method static  bool hasTable(string $table)
 * @method static  void defaultStringLength(int $length)
 * @method static  array getColumnListing(string $table)
 * @method static  string getColumnType(string $table, string $column)
 * @method static  void morphUsingUuids()
 * @see \ORPTech\MigrationPartition\Database\Schema\Builder
 */
class Schema extends Facade
{
    /**
     * Indicates if the resolved facade should be cached.
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db.schema';
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = parent::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return (new Builder(static::$app['db']->connection()))->$method(...$args);
    }
}
