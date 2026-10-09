<?php

namespace ORPTech\MigrationPartition\Database\Concerns;

use Illuminate\Support\Fluent;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;

/**
 * MySQL / MariaDB keep partitions inside the partitioned table instead of as
 * separate tables, so the PostgreSQL style API is mapped as follows:
 *
 * - Range tables are created with a "{table}_default" partition holding values less than MAXVALUE.
 *   New range partitions are split off from it, so they must be created in ascending order.
 * - List tables are created with a "{table}_default" partition holding NULL, new list partitions are added next to it.
 * - Hash tables use KEY partitioning, which works for any column type. Each hash partition adds one more partition,
 *   the hash modulus and remainder are ignored because MySQL distributes rows by itself.
 * - Attaching moves the rows of an existing table into a new partition.
 * - Detaching moves the rows of a partition out into a new table named after the partition.
 */
trait CompilesMySqlPartitionQueries
{
    /**
     * Compile a create table command with its range partitions.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return array
     */
    public function compileCreateRangePartitioned(Blueprint $blueprint, Fluent $command): array
    {
        return $this->compileCreatePartitioned($blueprint, $command, sprintf(
            'partition by range columns (%s) (partition %s values less than (maxvalue))',
            $this->wrap($blueprint->rangeKey),
            $this->wrapPartition($blueprint, 'default')
        ));
    }

    /**
     * Compile a create table partition command for a range partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return string
     */
    public function compileCreateRangePartition(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileSplitDefaultRangePartition($blueprint, $this->wrapPartition($blueprint, $blueprint->suffixForPartition));
    }

    /**
     * Compile an attach partition command for a range partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return array
     */
    public function compileAttachRangePartition(Blueprint $blueprint, Fluent $command): array
    {
        return [
            $this->compileSplitDefaultRangePartition($blueprint, $this->wrap($blueprint->partitionTableName)),
            $this->compileExchangePartition($blueprint),
        ];
    }

    /**
     * Compile a create table command with its list partitions.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return array
     */
    public function compileCreateListPartitioned(Blueprint $blueprint, Fluent $command): array
    {
        return $this->compileCreatePartitioned($blueprint, $command, sprintf(
            'partition by list columns (%s) (partition %s values in (null))',
            $this->wrap($blueprint->listPartitionKey),
            $this->wrapPartition($blueprint, 'default')
        ));
    }

    /**
     * Compile a create table partition command for a list partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return string
     */
    public function compileCreateListPartition(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileAddListPartition($blueprint, $this->wrapPartition($blueprint, $blueprint->suffixForPartition));
    }

    /**
     * Compile an attach partition command for a list partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return array
     */
    public function compileAttachListPartition(Blueprint $blueprint, Fluent $command): array
    {
        return [
            $this->compileAddListPartition($blueprint, $this->wrap($blueprint->partitionTableName)),
            $this->compileExchangePartition($blueprint),
        ];
    }

    /**
     * Compile a create table command with its hash partitions.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return array
     */
    public function compileCreateHashPartitioned(Blueprint $blueprint, Fluent $command): array
    {
        return $this->compileCreatePartitioned($blueprint, $command, sprintf(
            'partition by key (%s) (partition %s)',
            $this->wrap($blueprint->hashPartitionKey),
            $this->wrapPartition($blueprint, 'default')
        ));
    }

    /**
     * Compile a create table partition command for a hash partitioned table.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return string
     */
    public function compileCreateHashPartition(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'alter table %s add partition (partition %s)',
            $this->wrapTable($blueprint),
            $this->wrapPartition($blueprint, $blueprint->suffixForPartition)
        );
    }

    /**
     * Get a list of all partitions of a table.
     *
     * @param string $table
     *
     * @return string
     */
    public function compileGetPartitions(string $table): string
    {
        return sprintf(
            'select partition_name as `tables` from information_schema.partitions where table_schema = database() and table_name = %s and partition_name is not null order by partition_ordinal_position',
            $this->quoteString($table)
        );
    }

    /**
     * Get all range partitioned tables.
     *
     * @return string
     */
    public function compileGetAllRangePartitionedTables(): string
    {
        return $this->compileGetAllPartitionedTables(['RANGE', 'RANGE COLUMNS']);
    }

    /**
     * Get all list partitioned tables.
     *
     * @return string
     */
    public function compileGetAllListPartitionedTables(): string
    {
        return $this->compileGetAllPartitionedTables(['LIST', 'LIST COLUMNS']);
    }

    /**
     * Get all hash partitioned tables.
     *
     * @return string
     */
    public function compileGetAllHashPartitionedTables(): string
    {
        return $this->compileGetAllPartitionedTables(['HASH', 'LINEAR HASH', 'KEY', 'LINEAR KEY']);
    }

    /**
     * Compile a detach query for a partitioned table. The partition rows are moved
     * into a new table named after the partition, then the partition is dropped.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     *
     * @return array
     */
    public function compileDetachPartition(Blueprint $blueprint, Fluent $command): array
    {
        $partition = $this->wrap($blueprint->partitionTableName);

        return [
            sprintf('create table %s like %s', $partition, $this->wrapTable($blueprint)),
            sprintf('alter table %s remove partitioning', $partition),
            $this->compileExchangePartition($blueprint),
            sprintf('alter table %s drop partition %s', $this->wrapTable($blueprint), $partition),
        ];
    }

    /**
     * Get the query that returns the partitioning method of a table.
     *
     * @param string $table
     *
     * @return string
     */
    public function compileGetPartitionMethod(string $table): string
    {
        return sprintf(
            'select partition_method from information_schema.partitions where table_schema = database() and table_name = %s and partition_name is not null limit 1',
            $this->quoteString($table)
        );
    }

    /**
     * Checks if the table should have a primary key.
     *
     * @param Blueprint $blueprint
     *
     * @return bool
     */
    public function shouldUsePrimaryKey(Blueprint $blueprint): bool
    {
        return $blueprint->pkCompositeOne && $blueprint->pkCompositeTwo;
    }

    /**
     * Compile a create table statement followed by the given partitioning clause.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @param string $partitioning
     *
     * @return array
     */
    protected function compileCreatePartitioned(Blueprint $blueprint, Fluent $command, string $partitioning): array
    {
        if ($primaryKey = $this->shouldUsePrimaryKey($blueprint))
        {
            // Registered as a command so auto incrementing columns don't declare a primary key of their own.
            $blueprint->primary([$blueprint->pkCompositeOne, $blueprint->pkCompositeTwo])->shouldBeSkipped = true;
        }

        $columns = $this->getColumns($blueprint);

        if ($primaryKey)
        {
            $columns[] = sprintf('primary key (%s)', $this->columnize([$blueprint->pkCompositeOne, $blueprint->pkCompositeTwo]));
        }

        $sql = sprintf('create table %s (%s)', $this->wrapTable($blueprint), implode(', ', $columns));
        $sql = $this->compileCreateEngine($this->compileCreateEncoding($sql, $blueprint), $blueprint);

        return [$sql.' '.$partitioning];
    }

    /**
     * Compile a statement that splits a new range partition off the default partition.
     *
     * @param Blueprint $blueprint
     * @param string $partition
     *
     * @return string
     */
    protected function compileSplitDefaultRangePartition(Blueprint $blueprint, string $partition): string
    {
        $default = $this->wrapPartition($blueprint, 'default');

        return sprintf(
            'alter table %s reorganize partition %s into (partition %s values less than (%s), partition %s values less than (maxvalue))',
            $this->wrapTable($blueprint),
            $default,
            $partition,
            $this->quotePartitionValue($blueprint->endDate),
            $default
        );
    }

    /**
     * Compile a statement that adds a new list partition.
     *
     * @param Blueprint $blueprint
     * @param string $partition
     *
     * @return string
     */
    protected function compileAddListPartition(Blueprint $blueprint, string $partition): string
    {
        return sprintf(
            'alter table %s add partition (partition %s values in (%s))',
            $this->wrapTable($blueprint),
            $partition,
            $this->quotePartitionValue($blueprint->listPartitionValue)
        );
    }

    /**
     * Compile a statement that swaps the rows of the partition with the table of the same name.
     *
     * @param Blueprint $blueprint
     *
     * @return string
     */
    protected function compileExchangePartition(Blueprint $blueprint): string
    {
        $partition = $this->wrap($blueprint->partitionTableName);

        return sprintf('alter table %s exchange partition %s with table %s', $this->wrapTable($blueprint), $partition, $partition);
    }

    /**
     * Get all tables partitioned with one of the given methods.
     *
     * @param array $methods
     *
     * @return string
     */
    protected function compileGetAllPartitionedTables(array $methods): string
    {
        return sprintf(
            'select distinct table_name as `tables` from information_schema.partitions where table_schema = database() and partition_method in (%s)',
            $this->quoteString($methods)
        );
    }

    /**
     * Wrap the name of a partition created for the table.
     *
     * @param Blueprint $blueprint
     * @param string $suffix
     *
     * @return string
     */
    protected function wrapPartition(Blueprint $blueprint, string $suffix): string
    {
        return $this->wrap($this->connection->getTablePrefix().$blueprint->getTable().'_'.$suffix);
    }

    /**
     * Quote and escape a partition boundary value, integers are left unquoted to match integer columns.
     *
     * @param string|int $value
     *
     * @return string
     */
    protected function quotePartitionValue(string|int $value): string
    {
        return is_int($value) ? (string) $value : "'".str_replace("'", "''", $value)."'";
    }
}
