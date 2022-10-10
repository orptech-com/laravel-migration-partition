<?php

namespace ORPTech\MigrationPartition;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ORPTech\MigrationPartition\Commands\PartitionRangeInitAllCommand;

class MigrationPartitionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-migration-partition')->hasCommand(PartitionRangeInitAllCommand::class);
    }
}
