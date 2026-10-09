<?php

namespace ORPTech\MigrationPartition\Database\Schema\Grammars;

use ORPTech\MigrationPartition\Database\Concerns\CompilesMySqlPartitionQueries;
use \Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;

class MySqlGrammar extends IlluminateMySqlGrammar
{
    use CompilesMySqlPartitionQueries;
}
