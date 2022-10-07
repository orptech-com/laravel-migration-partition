<?php

namespace ORPTech\MigrationPartition\Database\Schema;

use Illuminate\Support\Traits\Macroable;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    use Macroable;

    /**
     * Column key one for creating a composite key.
     *
     * @var bool
     */
    public $pkCompositeOne;

    /**
     * Column key two for creating a composite key.
     *
     * @var bool
     */
    public $pkCompositeTwo;

    /**
     * Partition range key for creating a partitioned table.
     *
     * @var bool
     */
    public $rangeKey;

    /**
     * Partition range key for creating a partitioned table.
     *
     * @var bool
     */
    public $subfixForPartition;

    /**
     * Partition range key for creating a partitioned table.
     *
     * @var bool
     */
    public $startDate;

    /**
     * Partition range key for creating a partitioned table.
     *
     * @var bool
     */
    public $endDate;


    /**
     * Determine if the blueprint has a create command.
     *
     * @return bool
     */
    public function creating()
    {
        return collect($this->commands)->contains(function ($command) {
            return $command->name === 'create' || 'createPartitioned';
        });
    }

    /**
     * Indicate that the table needs to be created with a partition.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function createPartitioned()
    {
        return $this->addCommand('createPartitioned');
    }

    /**
     * Create partition and attach parttioned table.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function attachPartition()
    {
        return $this->addCommand('attachPartition');
    }
}
