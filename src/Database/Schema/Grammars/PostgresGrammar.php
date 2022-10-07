<?php

namespace ORPTech\MigrationPartition\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;

class PostgresGrammar extends \Illuminate\Database\Schema\Grammars\PostgresGrammar
{
    /**
     * Compile a create table command with its partitions.
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileCreatePartitioned(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('%s table %s (%s) partition by range (%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            sprintf('%s, %s', implode(', ', $this->getColumns($blueprint)), sprintf('primary key (%s, %s)', $blueprint->pkCompositeOne, $blueprint->pkCompositeTwo)),
            $blueprint->rangeKey
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }
    /**
     * Compile a create table partition command for partitioned table
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return array
     */
    public function compileAttachPartition(Blueprint $blueprint, Fluent $command)
    {
        return array_values(array_filter(array_merge([sprintf('%s table %s_%s partition of %s for values from (\'%s\') to (\'%s\')',
            $blueprint->temporary ? 'create temporary' : 'create',
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->subfixForPartition,
            str_replace("\"", "", $this->wrapTable($blueprint)),
            $blueprint->startDate,
            $blueprint->endDate
        )], $this->compileAutoIncrementStartingValues($blueprint))));
    }
}
