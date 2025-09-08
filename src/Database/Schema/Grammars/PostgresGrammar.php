<?php

namespace ORPTech\MigrationPartition\Database\Schema\Grammars;

use ORPTech\MigrationPartition\Database\Concerns\CompilesPartitionQueries;
use \Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;

class PostgresGrammar extends IlluminatePostgresGrammar
{
    use CompilesPartitionQueries;
}
