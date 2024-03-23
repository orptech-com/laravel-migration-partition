<?php

namespace ORPTech\MigrationPartition\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use \Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;

class PostgresGrammar extends IlluminatePostgresGrammar
{
    /**
     * Compile a create table command with its range partitions.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return array
     */
    public function compileCreateRangePartitioned(Blueprint $blueprint, Fluent $command): array
    {
        return array_values(array_filter(array_merge([sprintf('create table %s (%s) partition by range (%s)',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->rangeKey
        )], [$this->compileAutoIncrementStartingValues($blueprint, $command)])));
    }

    /**
     * Compile a create table partition command for a range partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return array
     */
    public function compileCreateRangePartition(Blueprint $blueprint, Fluent $command): array
    {
        return array_values(array_filter(array_merge([sprintf('create table %s_%s partition of %s for values from (\'%s\') to (\'%s\')',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->suffixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->startDate,
            $blueprint->endDate
        )], [$this->compileAutoIncrementStartingValues($blueprint, $command)])));
    }

    /**
     * Compile a create table command with its list partitions.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileCreateListPartitioned(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('create table %s (%s) partition by list(%s)',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->listPartitionKey
        );
    }

    /**
     * Compile an attach partition command for a range partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileAttachRangePartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('ALTER table %s attach partition %s for values from (\'%s\') to (\'%s\')',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->partitionTableName,
            $blueprint->startDate,
            $blueprint->endDate
        );
    }

    /**
     * Compile a create table partition command for a list partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileCreateListPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('create table %s_%s partition of %s for values in (\'%s\')',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->suffixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->listPartitionValue,
        );
    }

    /**
     * Compile an attach partition command for a list partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileAttachListPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('alter table %s partition of %s for values in (\'%s\')',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->partitionTableName,
            $blueprint->listPartitionValue,
        );
    }

    /**
     * Compile a create table partition command for a hash partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileCreateHashPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('create table %s_%s partition of %s for values with (modulus %s, remainder %s)',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->suffixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->hashModulus,
            $blueprint->hashRemainder
        );
    }

    /**
     * Compile a create table command with its hash partitions.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return array
     */
    public function compileCreateHashPartitioned(Blueprint $blueprint, Fluent $command): array
    {
        return array_values(array_filter(array_merge([sprintf('create table %s (%s) partition by hash(%s)',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->hashPartitionKey
        )], [$this->compileAutoIncrementStartingValues($blueprint, $command)])));
    }

    /**
     * Compile an attach partition command for a hash partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileAttachHashPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('alter table %s partition of %s for values with (modulus %s, remainder %s)',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->partitionTableName,
            $blueprint->hashModulus,
            $blueprint->hashRemainder,
        );
    }

    /**
     * Get a list of all partitioned tables in the Database.
     * @param string $table
     * @return string
     */
    public function compileGetPartitions(string $table): string
    {
        return sprintf("SELECT inhrelid::regclass as tables
            FROM   pg_catalog.pg_inherits
            WHERE  inhparent = '%s'::regclass;",
            $table,
        );
    }

    /**
     * Get all range partitioned tables.
     * @return string
     */
    public function compileGetAllRangePartitionedTables(): string
    {
        return "select pg_class.relname as tables from pg_class inner join pg_partitioned_table on pg_class.oid = pg_partitioned_table.partrelid where pg_partitioned_table.partstrat = 'r';";
    }

    /**
     * Get all list partitioned tables.
     * @return string
     */
    public function compileGetAllListPartitionedTables(): string
    {
        return "select pg_class.relname as tables from pg_class inner join pg_partitioned_table on pg_class.oid = pg_partitioned_table.partrelid where pg_partitioned_table.partstrat = 'l';";
    }

    /**
     * Get all hash partitioned tables.
     * @return string
     */
    public function compileGetAllHashPartitionedTables(): string
    {
        return "select pg_class.relname as tables from pg_class inner join pg_partitioned_table on pg_class.oid = pg_partitioned_table.partrelid where pg_partitioned_table.partstrat = 'h';";
    }

    /**
     * Compile a detach query for a partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @return string
     */
    public function compileDetachPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('alter table %s detach partition %s',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->partitionTableName
        );
    }
}
