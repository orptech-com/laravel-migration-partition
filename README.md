<div>
    <p align="center"><a href="https://orptech.com" target="_blank"><img src="https://orptech.com/assets/images/logos/orptech-logo-white.png" width="150"></a></p>
    <p align="center"><a href="https://retrocket.io" target="_blank"><img src="https://retrocket.io/common/img/logo_white.png" width="150"></a></p>
</div>

# Database Partitions via Migrations for Laravel (aka Laravel Migration Partitions)
This package extends Illuminate to provide partitioned table creation in migrations for PostgreSQL, MySQL and MariaDB.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orptech/laravel-migration-partition.svg?style=flat-square)](https://packagist.org/packages/orptech/laravel-migration-partition)
[![Total Downloads](https://img.shields.io/packagist/dt/orptech/laravel-migration-partition.svg?style=flat-square)](https://packagist.org/packages/orptech/laravel-migration-partition)

## ORPtech Software

We are ORPtech. Here at ORPtech, we pride ourselves in ensuring that clients and services have a peaceful, safe and smooth interaction. We know how difficult it is to build trust for a service, therefore we are here to help bridge the gaps within the market via our robust applications. Please contact us for further information on how our services might help your business.

## Version Matching
| **Package Version** | **Supported**  |
|---------------------|------------|
| 11.x.x              | **Laravel 11** |
| 12.x.x              | **Laravel 12** |
| 13.x.x              | **Laravel 13** |

## Installation

You can install the package via composer:

```bash
composer require orptech/laravel-migration-partition
```

## DBMS Support

- PostgreSQL
- MySQL
- MariaDB

### Planned Development

- SQL Server 2017+
- SQLite 3.8.8+

## Usage
The same API works for PostgreSQL, MySQL and MariaDB. The package picks the grammar from your connection's driver.

## MySQL / MariaDB
MySQL and MariaDB keep partitions inside the partitioned table instead of as separate tables, so a few things work differently:

- Range partitioned tables use `RANGE COLUMNS` and get a `{table}_default` partition for values below `MAXVALUE`. New range partitions are split off from it, so create them in ascending order. Only the end value is used. Use `dateTime` rather than `timestamp` columns as range keys.
- List partitioned tables use `LIST COLUMNS` and get a `{table}_default` partition that holds `NULL`. Pass integers (not numeric strings) as list values for integer columns.
- Hash partitioned tables use `KEY` partitioning. Each `createHashPartition` call adds one partition. MySQL ignores the modulus and remainder because it distributes rows itself. You can't attach or detach hash partitions.
- `attachRangePartition` / `attachListPartition` add a partition named after the table you pass and move that table's rows into it. The table stays behind, empty.
- `detachPartition` moves the partition's rows into a new table named after the partition, then drops the partition.
- Every primary/unique key must include the partition column, as in PostgreSQL. You can keep `$table->id()` with a composite key such as `'id', 'created_at'`.

## PostgreSQL
PostgreSQL also known as Postgres, is a free and open-source relational database management system (RDBMS) emphasizing extensibility and SQL compliance.

### Range Partitioning 
Instead of importing Illuminate's Schema import this package's schema:
```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
```

## Template Usage

### Range Partition

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createRangePartitioned('[YourTableNameHere]', function (Blueprint $table) {
    //...
}, '[compositeKeyOne]', '[compositeKeyTwo]', '[rangePartitionKey]');
```

Enter null for `[compositeKeyOne]`, `[compositeKeyTwo]` if you don't need a primary key.

##### Creating a Range Partition for a Partitioned Table

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createRangePartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[suffixForPartition]', '[startDate]', '[endDate]');
```

##### Attaching a Range Partition to a Partitioned Table

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::attachRangePartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[suffixForPartition]', '[startDate]', '[endDate]');
```

### List Partition

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createListPartitioned('[YourTableNameHere]', function (Blueprint $table) {
    //...
}, '[compositeKeyOne]', '[compositeKeyTwo]', '[listPartitionKey]');
```

Enter null for `[compositeKeyOne]`, `[compositeKeyTwo]` if you don't need a primary key.

##### Creating a List Partition for a Partitioned Table

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createListPartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[suffixForPartition]', '[listPartitionValue]');
```

##### Attaching a List Partition to a Partitioned Table

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::attachListPartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[suffixForPartition]', '[listPartitionValue]');
```

### Hash Partition

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createHashPartitioned('[YourTableNameHere]', function (Blueprint $table) {
    //...
}, '[compositeKeyOne]', '[compositeKeyTwo]', '[hashPartitionKey]');
```

Enter null for `[compositeKeyOne]`, `[compositeKeyTwo]` if you don't need a primary key.

##### Creating a Hash Partition for a Partitioned Table

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createHashPartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[suffixForPartition]', '[hashModulus]', '[hashRemainder]');
```

##### Attaching a Hash Partition to a Partitioned Table

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::attachHashPartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[suffixForPartition]', '[hashModulus]', '[hashRemainder]');
```

#### Removing a Partition

```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::detachPartition('[YourPartitionedTableNameHere]', function (Blueprint $table) {}, '[partitionTableName]');
```

## Commands

##### New Series of Range Partition Migrations
This command will create a new series of migrations for all range partitioned tables.
```bash
php artisan partition:range
```

##### New Series of List Partition Migrations
This command will create a new series of migrations for all list partitioned tables.
```bash
php artisan partition:list
```

##### New Series of Hash Partition Migrations
This command will create a new series of migrations for all hash partitioned tables.
```bash
php artisan partition:hash
```

##### Listing Partitions
This command will list all the partitioned tables.
```bash
php artisan partition:partitions
```

### Important
- This package currently supports PostgreSQL Range Partitions.
- You shouldn't define any primary keys in your migration. The package creates a composite key while setting up the table.
- You need to create an initial partition to start using the tables. (PostgreSQL)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ORPtech](https://github.com/orptech-com)
- [Retrocket](https://github.com/retrocket)
- [Laravel](https://github.com/laravel)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
