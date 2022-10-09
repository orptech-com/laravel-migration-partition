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
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileCreateRangePartitioned(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('create table %s (%s) partition by range (%s)',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->rangeKey
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }
    /**
     * Compile a create table partition command for a range partitioned table.
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileAttachRangePartition(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('create table %s_%s partition of %s for values from (\'%s\') to (\'%s\')',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->subfixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->startDate,
            $blueprint->endDate
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }

    /**
     * Compile a create table command with its list partitions.
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileCreateListPartitioned(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('create table %s (%s) partition by list(%s)',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->listPartitionKey
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }
    /**
     * Compile a create table partition command for a list partitioned table.
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileAttachListPartition(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('create table %s_%s partition of %s for values in (\'%s\')',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->subfixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->listPartitionValue,
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }

    /**
     * Compile a create table command with its hash partitions.
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileCreateHashPartitioned(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('create table %s (%s) partition by hash(%s)',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->hashPartitionKey
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }
    /**
     * Compile a create table partition command for a hash partitioned table.
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileAttachHasPartition(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('create table %s_%s partition of %s for values with (modulus %s, remainder %s)',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->subfixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->hashModulus,
            $blueprint->hashRemainder,
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }
}
