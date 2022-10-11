<?php

namespace ORPTech\MigrationPartition\Commands;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use ORPTech\MigrationPartition\Database\Schema\Blueprint;
use ORPTech\MigrationPartition\Database\Schema\Builder;
use ORPTech\MigrationPartition\Support\Facades\Schema;

class PartitionRangeInitAllCommand extends Command
{

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

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
     * The console command description.
     *
     * @var string
     */
    protected $table;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $subfix;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $start;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $end;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $column;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->subfix = $this->ask('Define your range name (year, mount, etc... for subfix)');
        $this->start = $this->ask('Range start value');
        $this->end = $this->ask('Range end value');
        $this->column = $this->ask('Partition coloumn name');
        $tables = Schema::getAllRangePartitionedTables();
        foreach($tables as $table){
            $this->table = $table;
            $path = $this->getSourceFilePath();
            $contents = $this->getSourceFile();
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");

        }
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return base_path('database/migrations') . '/'.now()->format('Y_m_d_His').'_cretate_partition_' . $this->table.'_'.$this->subfix . '_table.php';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__.'/Stubs/range-partition-migration.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables()
    {
        return [
            'TABLE' => $this->table,
            'SUBFIX' => $this->subfix,
            'START' => $this->start,
            'END' => $this->end,
            'COLUMN' => $this->column,
        ];
    }




}
