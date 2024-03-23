<?php

namespace ORPTech\MigrationPartition\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Support\Fluent;

class Blueprint extends IlluminateBlueprint
{
    /**
     * Column key one for creating a composite key for a range partitioned table.
     *
     * @var string
     */
    public string $pkCompositeOne;

    /**
     * Column key two for creating a composite key for range partitioned table.
     *
     * @var string
     */
    public string $pkCompositeTwo;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public string $rangeKey;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public string $suffixForPartition;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public string $startDate;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public string $endDate;

    /**
     * Column key for creating a table with list partition.
     *
     * @var string
     */
    public string $listPartitionKey;

    /**
     * Column key for creating list partitions.
     *
     * @var string
     */
    public string $listPartitionValue;

    /**
     * Column key for creating a table with hash partition.
     *
     * @var string
     */
    public string $hashPartitionKey;

    /**
     * Hashing modulus for creating a hash partition.
     *
     * @var int
     */
    public int $hashModulus;

    /**
     * Hashing reminder for creating a hash partition.
     *
     * @var int
     */
    public int $hashRemainder;

    /**
     * Column key for creating a table with list partition.
     *
     * @var string
     */
    public string $partitionTableName;

    /**
     * List of commands that trigger the creating function.
     *
     * @var array
     */
    private array $creators = ['create', 'createRangePartitioned', 'createListPartitioned', 'createHashPartitioned'];

    /**
     * Determine if the blueprint has a create command.
     *
     * @return bool
     */
    public function creating(): bool
    {
        return collect($this->commands)->contains(function ($command) {
            return in_array($command->name, $this->creators, false);
        });
    }

    /**
     * Indicate that the table needs to be created with a range partition.
     *
     * @return Fluent
     */
    public function createRangePartitioned(): Fluent
    {
        return $this->addCommand('createRangePartitioned');
    }

    /**
     * Create a range partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function createRangePartition(): Fluent
    {
        return $this->addCommand('createRangePartition');
    }

    /**
     * Attach a range partition for existing table to the partitioned table.
     *
     * @return Fluent
     */
    public function attachRangePartition(): Fluent
    {
        return $this->addCommand('attachRangePartition');
    }

    /**
     * Indicate that the table needs to be created with a list partition.
     *
     * @return Fluent
     */
    public function createListPartitioned(): Fluent
    {
        return $this->addCommand('createListPartitioned');
    }

    /**
     * Attach a range partition for existing table to the partitioned table.
     *
     * @return Fluent
     */
    public function attachListPartition(): Fluent
    {
        return $this->addCommand('attachListPartition');
    }

    /**
     * Create a list partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function createListPartition(): Fluent
    {
        return $this->addCommand('createListPartition');
    }

    /**
     * Indicate that the table needs to be created with a hash partition.
     *
     * @return Fluent
     */
    public function createHashPartitioned(): Fluent
    {
        return $this->addCommand('createHashPartitioned');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function createHashPartition(): Fluent
    {
        return $this->addCommand('createHashPartition');
    }
    /**
     * Attach a range partition for existing table to the partitioned table.
     *
     * @return Fluent
     */
    public function attachHashPartition(): Fluent
    {
        return $this->addCommand('attachHashPartition');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function getAllRangePartitionedTables(): Fluent
    {
        return $this->addCommand('getAllRangePartitionedTables');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function getAllHashPartitionedTables(): Fluent
    {
        return $this->addCommand('getAllHashPartitionedTables');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function getAllListPartitionedTables(): Fluent
    {
        return $this->addCommand('getAllListPartitionedTables');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return Fluent
     */
    public function getPartitions(): Fluent
    {
        return $this->addCommand('getPartitions');
    }
    /**
     * Indicate that the table needs to be created with a range partition.
     *
     * @return Fluent
     */
    public function detachPartition(): Fluent
    {
        return $this->addCommand('detachPartition');
    }

}
