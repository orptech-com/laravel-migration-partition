<?php

use Illuminate\Support\Facades\DB;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use ORPTech\MigrationPartition\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Statements are captured with pretend mode, so no database server is needed.
 */
function partitionConnection(string $driver): string
{
    config()->set("database.connections.partition_{$driver}", [
        'driver' => $driver,
        'database' => 'test',
        'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
        'prefix' => '',
    ]);

    return "partition_{$driver}";
}

function pretendSql(string $driver, Closure $callback): array
{
    $connection = DB::connection(partitionConnection($driver));

    return array_column($connection->pretend(fn () => $callback(new Builder($connection))), 'query');
}
