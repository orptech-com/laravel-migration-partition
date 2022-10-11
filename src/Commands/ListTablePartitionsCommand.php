<?php

namespace ORPTech\MigrationPartition\Commands;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use ORPTech\MigrationPartition\Support\Facades\Schema;

class ListTablePartitionsCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partition:list';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $table;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->table = $this->ask('Table name');
        $tables = Schema::getPartitions($this->table);
        foreach($tables as $table){
            $this->info($table);
        }
    }
}
