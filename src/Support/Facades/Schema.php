<?php

namespace ORPTech\MigrationPartition\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use RuntimeException;

/**
 * Range partitioning related methods.
 * @method static  createRangePartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $rangeKey)
 * @method static  createRangePartition(string $table, \Closure $callback, string $suffixForPartition, string $startDate, string $endDate)
 * @method static  attachRangePartition(string $table, \Closure $callback, string $partitionTableName, string $startDate, string $endDate)
 * @method static  getAllRangePartitionedTables()
 * List partitioning related methods.
 * @method static  createListPartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $listPartitionKey)
 * @method static  createListPartition(string $table, \Closure $callback, string $suffixForPartition, string $listPartitionValue)
 * @method static  attachListPartition(string $table, \Closure $callback, string $partitionTableName, string $listPartitionValue)
 * @method static  getAllListPartitionedTables()
 * Hash partitioning related methods.
 * @method static  createHashPartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $hashPartitionKey)
 * @method static  createHashPartition(string $table, \Closure $callback, string $suffixForPartition, int $hashModulus, int $hashRemainder)
 * @method static  attachHashPartition(string $table, \Closure $callback, string $partitionTableName, int $hashModulus, int $hashRemainder)
 * @method static  getAllHashPartitionedTables()
 * General partitioning methods.
 * @method static  getPartitions(string $table)
 * @method static  detachPartition(string $table, \Closure $callback, string $partitionTableName)
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
