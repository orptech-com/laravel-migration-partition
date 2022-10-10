<?php

namespace ORPTech\MigrationPartition\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use ORPTech\MigrationPartition\Support\Facades\Schema;

class PartitionRangeInitAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partition:range-init-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $subfix = $this->ask('Define your range name (year, mount, etc... for subfix)');
        $start = $this->ask('Range start value');
        $end = $this->ask('Range end value');
        $column = $this->ask('Partition coloumn name');
        $tables = Schema::getAllRangePartitionedTables();
        foreach($tables as $table){
            Schema::initRangePartition($table, function (Blueprint $table) use($subfix, $start, $end) {
            }, $subfix, $start, $end);
            $this->line($table.'_'.$subfix.' created!');
        }
    }

}
