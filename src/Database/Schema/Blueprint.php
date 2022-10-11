<?php

namespace ORPTech\MigrationPartition\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;

class Blueprint extends IlluminateBlueprint
{
    /**
     * Column key one for creating a composite key for a range partitioned table.
     *
     * @var string
     */
    public $pkCompositeOne;

    /**
     * Column key two for creating a composite key for range partitioned table.
     *
     * @var string
     */
    public $pkCompositeTwo;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public $rangeKey;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public $subfixForPartition;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public $startDate;

    /**
     * Partition range key for creating a range partitioned table.
     *
     * @var string
     */
    public $endDate;

    /**
     * Column key for creating a table with list partition.
     *
     * @var string
     */
    public $listPartitionKey;

    /**
     * Column key for creating list partitions.
     *
     * @var string
     */
    public $listPartitionValue;

    /**
     * Column key for creating a table with hash partition.
     *
     * @var string
     */
    public $hashPartitionKey;

    /**
     * Hashing modulus for creating a hash partition.
     *
     * @var int
     */
    public $hashModulus;

    /**
     * Hashing reminder for creating a hash partition.
     *
     * @var int
     */
    public $hashRemainder;

    /**
     * Column key for creating a table with list partition.
     *
     * @var string
     */
    public $partitionTableName;

    /**
     * List of commands that trigger the creating function.
     *
     * @var array
     */
    private $creators = ['create', 'createRangePartitioned', 'createListPartitioned', 'createHashPartitioned'];

    /**
     * Determine if the blueprint has a create command.
     *
     * @return bool
     */
    public function creating()
    {
        return collect($this->commands)->contains(function ($command) {
            return in_array($command->name, $this->creators, false);
        });
    }

    /**
     * Indicate that the table needs to be created with a range partition.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createRangePartitioned()
    {
        return $this->addCommand('createRangePartitioned');
    }

    /**
     * Create a range partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createRangePartition()
    {
        return $this->addCommand('createRangePartition');
    }

    /**
     * Attach a range partition for existing table to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function attachRangePartition()
    {
        return $this->addCommand('attachRangePartition');
    }

    /**
     * Indicate that the table needs to be created with a list partition.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createListPartitioned()
    {
        return $this->addCommand('createListPartitioned');
    }

    /**
     * Attach a range partition for existing table to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function attachListPartition()
    {
        return $this->addCommand('attachListPartition');
    }

    /**
     * Create a list partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createListPartition()
    {
        return $this->addCommand('createListPartition');
    }

    /**
     * Indicate that the table needs to be created with a hash partition.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createHashPartitioned()
    {
        return $this->addCommand('createHashPartitioned');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createHashPartition()
    {
        return $this->addCommand('createHashPartition');
    }
    /**
     * Attach a range partition for existing table to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function attachHashPartition()
    {
        return $this->addCommand('attachHashPartition');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function getAllRangePartitionedTables()
    {
        return $this->addCommand('getAllRangePartitionedTables');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function getAllHashPartitionedTables()
    {
        return $this->addCommand('getAllHashPartitionedTables');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function getAllListPartitionedTables()
    {
        return $this->addCommand('getAllListPartitionedTables');
    }

    /**
     * Create a hash partition and attach it to the partitioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function getPartitions()
    {
        return $this->addCommand('getPartitions');
    }
    /**
     * Indicate that the table needs to be created with a range partition.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function detachPartition()
    {
        return $this->addCommand('detachPartition');
    }

}
