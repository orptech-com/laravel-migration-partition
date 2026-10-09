<?php

use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Database\Schema\Builder;

it('creates a range partitioned table with a composite primary key', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->createRangePartitioned('logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('created_at');
        }, 'id', 'created_at', 'created_at');
    });

    expect($sql)->toHaveCount(1)
        ->and($sql[0])
        ->toStartWith('create table "logs" (')
        ->toContain('"id" bigserial not null,')
        ->not->toContain('bigserial not null primary key')
        ->toEndWith(', primary key (id, created_at)) partition by range (created_at)');
});

it('creates partitioned tables without a primary key', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->createListPartitioned('users', function (Blueprint $table) {
            $table->string('country');
        }, null, null, 'country');
        $schema->createHashPartitioned('events', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
        }, null, null, 'user_id');
    });

    expect($sql)->toHaveCount(2)
        ->and($sql[0])->toStartWith('create table "users" (')->toEndWith(') partition by list(country)')->not->toContain('primary key')
        ->and($sql[1])->toStartWith('create table "events" (')->toEndWith(') partition by hash(user_id)')->not->toContain('primary key');
});

it('creates list and hash partitioned tables with a composite primary key', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->createListPartitioned('users', function (Blueprint $table) {
            $table->id();
            $table->string('country');
        }, 'id', 'country', 'country');
        $schema->createHashPartitioned('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        }, 'id', 'user_id', 'user_id');
    });

    expect($sql[0])->toEndWith(', primary key (id, country)) partition by list(country)')
        ->and($sql[1])->toEndWith(', primary key (id, user_id)) partition by hash(user_id)');
});

it('sets the auto increment starting value of a partitioned table', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->createRangePartitioned('logs', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->dateTime('created_at');
        }, 'id', 'created_at', 'created_at');
    });

    expect($sql)->toHaveCount(2)
        ->and($sql[1])->toBe("select setval(pg_get_serial_sequence('\"logs\"', 'id'), 1000, false)");
});

it('creates range, list and hash partitions', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->createRangePartition('logs', function (Blueprint $table) {}, 'y2024', '2024-01-01', '2025-01-01');
        $schema->createListPartition('users', function (Blueprint $table) {}, 'tr', 'TR');
        $schema->createHashPartition('events', function (Blueprint $table) {}, 'p1', 2, 1);
    });

    expect($sql)->toBe([
        "create table logs_y2024 partition of logs for values from ('2024-01-01') to ('2025-01-01')",
        "create table users_tr partition of users for values in ('TR')",
        'create table events_p1 partition of events for values with (modulus 2, remainder 1)',
    ]);
});

it('attaches range, list and hash partitions', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->attachRangePartition('logs', function (Blueprint $table) {}, 'logs_y2024', '2024-01-01', '2025-01-01');
        $schema->attachListPartition('users', function (Blueprint $table) {}, 'users_tr', 'TR');
        $schema->attachHashPartition('events', function (Blueprint $table) {}, 'events_p1', 2, 1);
    });

    expect($sql)->toBe([
        "ALTER table logs attach partition logs_y2024 for values from ('2024-01-01') to ('2025-01-01')",
        "alter table users attach partition users_tr for values in ('TR')",
        'alter table events attach partition events_p1 for values with (modulus 2, remainder 1)',
    ]);
});

it('detaches a partition', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        $schema->detachPartition('logs', function (Blueprint $table) {}, 'logs_y2024');
    });

    expect($sql)->toBe(['alter table logs detach partition logs_y2024']);
});

it('queries partitions and partitioned tables on its own connection', function () {
    $sql = pretendSql('pgsql', function (Builder $schema) {
        expect($schema->getPartitions('logs'))->toBe([])
            ->and($schema->getAllRangePartitionedTables())->toBe([])
            ->and($schema->getAllListPartitionedTables())->toBe([])
            ->and($schema->getAllHashPartitionedTables())->toBe([]);
    });

    expect($sql)->toHaveCount(4)
        ->and($sql[0])->toContain('pg_catalog.pg_inherits')->toContain("inhparent = 'logs'::regclass")
        ->and($sql[1])->toContain("partstrat = 'r'")
        ->and($sql[2])->toContain("partstrat = 'l'")
        ->and($sql[3])->toContain("partstrat = 'h'");
});
