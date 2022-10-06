<div>
    <p align="center"><a href="https://orptech.com" target="_blank"><img src="https://orptech.com/elements/img/orptech-logo.png" width="150"></a></p>
    <p align="center"><a href="https://retrocket.io" target="_blank"><img src="https://www.retrocket.io/common/img/logo_white.png" width="150"></a></p>
</div>

# Laravel Partitions for Migrations
This package extends Illuminate to provide partitioned table creation in migrations.


## ORPTech

We are ORPTech. Here at ORPTech, we pride ourselves in ensuring that clients and services have a peaceful, safe and smooth interaction. We know how difficult it is to build trust for a service, therefore we are here to help bridge the gaps within the market via our robust applications. Please contact us for further information on how our services might protect your business.

## Installation

You can install the package via composer:

```bash
composer require orptech/laravel-migration-partition
```

## Supported DBMS List

- PostgreSQL


## Usage

Instead of importing Illuminate's Schema import this package's schema:
```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
```

### Template Usage
```php
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Support\Facades\Schema;

Schema::createPartitioned('[YourTableNameHere]', function (Blueprint $table) {
    //...
}, '[compositeKeyOne]', '[compositeKeyTwo]', '[rangePartitionKey]');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ORPTech](https://github.com/orptech-com)
- [Retrocket](https://github.com/retrocket)
- [Laravel](https://github.com/laravel)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
