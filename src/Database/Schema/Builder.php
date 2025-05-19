<?php

namespace ORPTech\MigrationPartition\Database\Schema;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Connection;
use ORPTech\MigrationPartition\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Builder as IlluminateBuilder;

class Builder extends IlluminateBuilder
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->grammar = new PostgresGrammar();
    }

    /**
     * Create a new table on the schema with range partitions.
     *
     * @param string $table
     * @param Closure $callback
     * @param string|null $pkCompositeOne
     * @param string|null $pkCompositeTwo
     * @param string $rangeKey
     * @return void
     * @throws BindingResolutionException
     */
    public function createRangePartitioned(string $table, Closure $callback, ?string $pkCompositeOne, ?string $pkCompositeTwo, string $rangeKey): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $pkCompositeOne, $pkCompositeTwo, $rangeKey) {
            $blueprint->createRangePartitioned();
            $blueprint->pkCompositeOne = $pkCompositeOne;
            $blueprint->pkCompositeTwo = $pkCompositeTwo;
            $blueprint->rangeKey = $rangeKey;

            $callback($blueprint);
        }));
    }

    /**
     * Create a new range partition on the table.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $suffixForPartition
     * @param string $startDate
     * @param string $endDate
     * @return void
     * @throws BindingResolutionException
     */
    public function createRangePartition(string $table, Closure $callback, string $suffixForPartition, string $startDate, string $endDate): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $suffixForPartition, $startDate, $endDate) {
            $blueprint->createRangePartition();
            $blueprint->suffixForPartition = $suffixForPartition;
            $blueprint->startDate = $startDate;
            $blueprint->endDate = $endDate;

            $callback($blueprint);
        }));
    }

    /**
     * Attach a new range partition to a partitioned table.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $partitionTableName
     * @param string $startDate
     * @param string $endDate
     * @return void
     * @throws BindingResolutionException
     */
    public function attachRangePartition(string $table, Closure $callback, string $partitionTableName, string $startDate, string $endDate): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $partitionTableName, $startDate, $endDate) {
            $blueprint->attachRangePartition();
            $blueprint->partitionTableName = $partitionTableName;
            $blueprint->startDate = $startDate;
            $blueprint->endDate = $endDate;
            $callback($blueprint);
        }));
    }

    /**
     * Create a new table on the schema with list partitions.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $pkCompositeOne
     * @param string $pkCompositeTwo
     * @param string $listPartitionKey
     * @return void
     * @throws BindingResolutionException
     */
    public function createListPartitioned(string $table, Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $listPartitionKey): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $pkCompositeOne, $pkCompositeTwo, $listPartitionKey) {
            $blueprint->createListPartitioned();
            $blueprint->pkCompositeOne = $pkCompositeOne;
            $blueprint->pkCompositeTwo = $pkCompositeTwo;
            $blueprint->listPartitionKey = $listPartitionKey;

            $callback($blueprint);
        }));
    }

    /**
     * Create a list partition on the table.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $suffixForPartition
     * @param string $listPartitionValue
     * @return void
     * @throws BindingResolutionException
     */
    public function createListPartition(string $table, Closure $callback, string $suffixForPartition, string $listPartitionValue): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $suffixForPartition, $listPartitionValue) {
            $blueprint->createListPartition();
            $blueprint->suffixForPartition = $suffixForPartition;
            $blueprint->listPartitionValue = $listPartitionValue;

            $callback($blueprint);
        }));
    }

    /**
     * Attach a new list partition.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $partitionTableName
     * @param string $listPartitionValue
     * @return void
     * @throws BindingResolutionException
     */
    public function attachListPartition(string $table, Closure $callback, string $partitionTableName, string $listPartitionValue): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $partitionTableName, $listPartitionValue) {
            $blueprint->attachListPartition();
            $blueprint->partitionTableName = $partitionTableName;
            $blueprint->listPartitionValue = $listPartitionValue;
            $callback($blueprint);
        }));
    }

    /**
     * Create a table on the schema with hash partitions.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $pkCompositeOne
     * @param string $pkCompositeTwo
     * @param string $hashPartitionKey
     * @return void
     * @throws BindingResolutionException
     */
    public function createHashPartitioned(string $table, Closure $callback, string $pkCompositeOne, string $pkCompositeTwo, string $hashPartitionKey): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $pkCompositeOne, $pkCompositeTwo, $hashPartitionKey) {
            $blueprint->createHashPartitioned();
            $blueprint->pkCompositeOne = $pkCompositeOne;
            $blueprint->pkCompositeTwo = $pkCompositeTwo;
            $blueprint->hashPartitionKey = $hashPartitionKey;
            $callback($blueprint);
        }));
    }


    /**
     * Create and attach a new hash partition on the table.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $suffixForPartition
     * @param int $hashModulus
     * @param int $hashRemainder
     * @return void
     * @throws BindingResolutionException
     */
    public function createHashPartition(string $table, Closure $callback, string $suffixForPartition, int $hashModulus, int $hashRemainder): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $suffixForPartition, $hashModulus, $hashRemainder) {
            $blueprint->createHashPartition();
            $blueprint->suffixForPartition = $suffixForPartition;
            $blueprint->hashModulus = $hashModulus;
            $blueprint->hashRemainder = $hashRemainder;
            $callback($blueprint);
        }));
    }

    /**
     * Attach a hash partition.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $partitionTableName
     * @param int $hashModulus
     * @param int $hashRemainder
     * @return void
     * @throws BindingResolutionException
     */
    public function attachHashPartition(string $table, Closure $callback, string $partitionTableName, int $hashModulus, int $hashRemainder): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $partitionTableName, $hashModulus, $hashRemainder) {
            $blueprint->attachHashPartition();
            $blueprint->partitionTableName = $partitionTableName;
            $blueprint->hashModulus = $hashModulus;
            $blueprint->hashRemainder = $hashRemainder;
            $callback($blueprint);
        }));
    }

    /**
     * Get all the partitioned table names for the database.
     *
     * @param  string  $table
     * @return array
     */
    public function getPartitions(string $table): array
    {
        return  array_column(DB::select($this->grammar->compileGetPartitions($table)), 'tables');
    }

    /**
     * Get all the range partitioned table names for the database.
     *
     * @return array
     */
    public function getAllRangePartitionedTables(): array
    {
        return  array_column(DB::select($this->grammar->compileGetAllRangePartitionedTables()), 'tables');
    }

    /**
     * Get all the list partitioned table names for the database.
     *
     * @return array
     */
    public function getAllListPartitionedTables(): array
    {
        return  array_column(DB::select($this->grammar->compileGetAllListPartitionedTables()), 'tables');
    }

    /**
     * Get all the hash partitioned table names for the database.
     *
     * @return array
     */
    public function getAllHashPartitionedTables(): array
    {
        return  array_column(DB::select($this->grammar->compileGetAllHashPartitionedTables()), 'tables');
    }

    /**
     * Detaches a partition from a partitioned table.
     *
     * @param string $table
     * @param Closure $callback
     * @param string $partitionTableName
     * @return void
     * @throws BindingResolutionException
     */
    public function detachPartition(string $table, Closure $callback, string $partitionTableName): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback, $partitionTableName) {
            $blueprint->detachPartition();
            $blueprint->partitionTableName = $partitionTableName;
            $callback($blueprint);
        }));
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param string $table
     * @param Closure|null $callback
     * @return Closure|mixed|object|Blueprint|null
     * @throws BindingResolutionException
     */
    protected function createBlueprint($table, Closure $callback = null): mixed
    {
        $prefix = $this->connection->getConfig('prefix_indexes')
            ? $this->connection->getConfig('prefix')
            : '';

        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }

        return Container::getInstance()->make(Blueprint::class, compact('table', 'callback', 'prefix'));
    }

}
