<?php

use Illuminate\Support\Facades\DB;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Database\Schema\Builder;

dataset('drivers', ['mysql', 'mariadb']);

it('creates a range partitioned table with a default partition', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createRangePartitioned('logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('created_at');
        }, 'id', 'created_at', 'created_at');
    });

    expect($sql)->toHaveCount(1)
        ->and($sql[0])
        ->toStartWith('create table `logs` (')
        ->toContain('`id` bigint unsigned not null auto_increment,')
        ->not->toContain('auto_increment primary key')
        ->toContain(', primary key (`id`, `created_at`)) default character set utf8mb4')
        ->toEndWith(' partition by range columns (`created_at`) (partition `logs_default` values less than (maxvalue))');
})->with('drivers');

it('creates a list partitioned table with a default partition', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createListPartitioned('users', function (Blueprint $table) {
            $table->string('country');
        }, null, null, 'country');
    });

    expect($sql)->toHaveCount(1)
        ->and($sql[0])
        ->not->toContain('primary key')
        ->toEndWith(' partition by list columns (`country`) (partition `users_default` values in (null))');
})->with('drivers');

it('creates a hash partitioned table with key partitioning', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createHashPartitioned('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        }, 'id', 'user_id', 'user_id');
    });

    expect($sql)->toHaveCount(1)
        ->and($sql[0])
        ->toContain(', primary key (`id`, `user_id`))')
        ->toEndWith(' partition by key (`user_id`) (partition `events_default`)');
})->with('drivers');

it('uses the table engine and collation', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createHashPartitioned('events', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->collation('utf8mb4_bin');
            $table->unsignedBigInteger('user_id');
        }, null, null, 'user_id');
    });

    expect($sql[0])->toContain(") default character set utf8mb4 collate 'utf8mb4_bin' engine = InnoDB partition by key");
})->with('drivers');

it('sets the auto increment starting value of a partitioned table', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createRangePartitioned('logs', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->dateTime('created_at');
        }, 'id', 'created_at', 'created_at');
    });

    expect($sql)->toHaveCount(2)
        ->and($sql[1])->toBe('alter table `logs` auto_increment = 1000');
})->with('drivers');

it('splits range partitions off the default partition', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createRangePartition('logs', function (Blueprint $table) {}, 'y2024', '2024-01-01', '2025-01-01');
        $schema->createRangePartition('orders', function (Blueprint $table) {}, 'first', 0, 1000);
    });

    expect($sql)->toBe([
        "alter table `logs` reorganize partition `logs_default` into (partition `logs_y2024` values less than ('2025-01-01'), partition `logs_default` values less than (maxvalue))",
        'alter table `orders` reorganize partition `orders_default` into (partition `orders_first` values less than (1000), partition `orders_default` values less than (maxvalue))',
    ]);
})->with('drivers');

it('adds list and hash partitions', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createListPartition('users', function (Blueprint $table) {}, 'tr', 'TR');
        $schema->createListPartition('orders', function (Blueprint $table) {}, 'shop_1', 1);
        $schema->createHashPartition('events', function (Blueprint $table) {}, 'p1', 2, 1);
    });

    expect($sql)->toBe([
        "alter table `users` add partition (partition `users_tr` values in ('TR'))",
        'alter table `orders` add partition (partition `orders_shop_1` values in (1))',
        'alter table `events` add partition (partition `events_p1`)',
    ]);
})->with('drivers');

it('escapes quotes in partition values', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->createListPartition('users', function (Blueprint $table) {}, 'x', "O'Reilly");
    });

    expect($sql)->toBe(["alter table `users` add partition (partition `users_x` values in ('O''Reilly'))"]);
})->with('drivers');

it('attaches range and list partitions by exchanging the table rows', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->attachRangePartition('logs', function (Blueprint $table) {}, 'logs_y2025', '2025-01-01', '2026-01-01');
        $schema->attachListPartition('users', function (Blueprint $table) {}, 'users_de', 'DE');
    });

    expect($sql)->toBe([
        "alter table `logs` reorganize partition `logs_default` into (partition `logs_y2025` values less than ('2026-01-01'), partition `logs_default` values less than (maxvalue))",
        'alter table `logs` exchange partition `logs_y2025` with table `logs_y2025`',
        "alter table `users` add partition (partition `users_de` values in ('DE'))",
        'alter table `users` exchange partition `users_de` with table `users_de`',
    ]);
})->with('drivers');

it('refuses to attach hash partitions', function (string $driver) {
    pretendSql($driver, function (Builder $schema) {
        $schema->attachHashPartition('events', function (Blueprint $table) {}, 'events_p1', 2, 1);
    });
})->with('drivers')->throws(RuntimeException::class, 'Attaching hash partitions is not supported on MySQL and MariaDB.');

it('detaches a partition into its own table', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        $schema->detachPartition('logs', function (Blueprint $table) {}, 'logs_y2024');
    });

    expect($sql)->toHaveCount(5)
        ->and($sql[0])->toContain('select partition_method from information_schema.partitions')->toContain("table_name = 'logs'")
        ->and(array_slice($sql, 1))->toBe([
            'create table `logs_y2024` like `logs`',
            'alter table `logs_y2024` remove partitioning',
            'alter table `logs` exchange partition `logs_y2024` with table `logs_y2024`',
            'alter table `logs` drop partition `logs_y2024`',
        ]);
})->with('drivers');

it('refuses to detach hash partitions', function (string $driver, string $method) {
    $connection = DB::connection(partitionConnection($driver));

    $schema = new class($connection, $method) extends Builder {
        public function __construct($connection, private string $partitionMethod)
        {
            parent::__construct($connection);
        }

        protected function getPartitionMethod(string $table): ?string
        {
            return $this->partitionMethod;
        }
    };

    $schema->detachPartition('events', function (Blueprint $table) {}, 'events_p1');
})->with('drivers')->with(['HASH', 'LINEAR HASH', 'KEY', 'LINEAR KEY'])
    ->throws(RuntimeException::class, 'Detaching hash partitions is not supported on MySQL and MariaDB.');

it('queries partitions and partitioned tables from information schema', function (string $driver) {
    $sql = pretendSql($driver, function (Builder $schema) {
        expect($schema->getPartitions('logs'))->toBe([])
            ->and($schema->getAllRangePartitionedTables())->toBe([])
            ->and($schema->getAllListPartitionedTables())->toBe([])
            ->and($schema->getAllHashPartitionedTables())->toBe([]);
    });

    expect($sql)->toBe([
        "select partition_name as `tables` from information_schema.partitions where table_schema = database() and table_name = 'logs' and partition_name is not null order by partition_ordinal_position",
        "select distinct table_name as `tables` from information_schema.partitions where table_schema = database() and partition_method in ('RANGE', 'RANGE COLUMNS')",
        "select distinct table_name as `tables` from information_schema.partitions where table_schema = database() and partition_method in ('LIST', 'LIST COLUMNS')",
        "select distinct table_name as `tables` from information_schema.partitions where table_schema = database() and partition_method in ('HASH', 'LINEAR HASH', 'KEY', 'LINEAR KEY')",
    ]);
})->with('drivers');
