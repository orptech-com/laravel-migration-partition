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
     * List of commands that trigger the creating function.
     *
     * @var array
     */
    private $creators = ['create', 'createRangePartitioned', 'createListPartitioned'];

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
     * Create range partition and attach it to the parttioned table.
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
     * Create list partition and attach it to the parttioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function attachListPartition()
    {
        return $this->addCommand('attachListPartition');
    }
}
