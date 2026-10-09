<?php

namespace ORPTech\MigrationPartition\Database\Schema\Grammars;

use ORPTech\MigrationPartition\Database\Concerns\CompilesMySqlPartitionQueries;
use \Illuminate\Database\Schema\Grammars\MariaDbGrammar as IlluminateMariaDbGrammar;

class MariaDbGrammar extends IlluminateMariaDbGrammar
{
    use CompilesMySqlPartitionQueries;
}
