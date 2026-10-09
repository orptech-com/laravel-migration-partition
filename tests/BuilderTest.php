<?php

use Illuminate\Support\Facades\DB;
use ORPTech\MigrationPartition\Commands\InitHashPartitionCommand;
use ORPTech\MigrationPartition\Commands\InitListPartitionCommand;
use ORPTech\MigrationPartition\Commands\InitRangePartitionCommand;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use ORPTech\MigrationPartition\Database\Schema\Grammars\MariaDbGrammar;
use ORPTech\MigrationPartition\Database\Schema\Grammars\MySqlGrammar;
use ORPTech\MigrationPartition\Database\Schema\Grammars\PostgresGrammar;
use ORPTech\MigrationPartition\Support\Facades\Schema;

it('picks the grammar for the connection driver', function (string $driver, string $grammar) {
    $connection = DB::connection(partitionConnection($driver));

    new Builder($connection);

    expect($connection->getSchemaGrammar())->toBeInstanceOf($grammar);
})->with([
    ['pgsql', PostgresGrammar::class],
    ['mysql', MySqlGrammar::class],
    ['mariadb', MariaDbGrammar::class],
]);

it('rejects unsupported drivers', function () {
    new Builder(DB::connection('testing'));
})->throws(RuntimeException::class, 'Partitioning is not supported for the [sqlite] driver.');

it('runs through the facade on the default connection', function (string $driver, string $expected) {
    config()->set('database.default', partitionConnection($driver));

    $sql = DB::connection()->pretend(function () {
        Schema::createListPartition('users', function (Blueprint $table) {}, 'tr', 'TR');
    });

    expect(array_column($sql, 'query'))->toBe([$expected]);
})->with([
    ['pgsql', "create table users_tr partition of users for values in ('TR')"],
    ['mysql', "alter table `users` add partition (partition `users_tr` values in ('TR'))"],
]);

it('renders valid partition migration stubs', function (string $command, array $variables, string $expected) {
    $command = app($command);

    $contents = $command->getStubContents($command->getStubPath(), $variables);

    expect($contents)
        ->toContain($expected)
        ->toMatch("/Schema::detachPartition\('logs', function \(Blueprint \\\$table\) \{\s+\}, 'logs_p1'\);/")
        ->toContain("Schema::dropIfExists('logs_p1');")
        ->not->toMatch('/\$[A-Z_]+\$/')
        ->and(fn () => token_get_all($contents, TOKEN_PARSE))->not->toThrow(ParseError::class);
})->with([
    'range' => [InitRangePartitionCommand::class, ['TABLE' => 'logs', 'SUFFIX' => 'p1', 'START' => '2024-01-01', 'END' => '2025-01-01'], "}, 'p1', '2024-01-01', '2025-01-01');"],
    'list' => [InitListPartitionCommand::class, ['TABLE' => 'logs', 'SUFFIX' => 'p1', 'LIST_KEY' => 'TR'], "}, 'p1', 'TR');"],
    'hash' => [InitHashPartitionCommand::class, ['TABLE' => 'logs', 'SUFFIX' => 'p1', 'MODULUS' => 2, 'REMAINDER' => 1], "}, 'p1', 2, 1);"],
]);
