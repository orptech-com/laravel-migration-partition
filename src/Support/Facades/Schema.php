<?php

namespace ORPTech\MigrationPartition\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use RuntimeException;

/**
 * @method static  createPartitioned(string $table, \Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $rangeKey)
 *
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
