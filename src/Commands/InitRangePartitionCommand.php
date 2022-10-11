<?php

namespace ORPTech\MigrationPartition\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use ORPTech\MigrationPartition\Support\Facades\Schema;

class InitRangePartitionCommand extends Command
{
    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected Filesystem $files;

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
    protected $signature = 'partition:range';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create a new series of migrations for all range partitioned table.';

    /**
     * The table name.
     *
     * @var string
     */
    protected $table;

    /**
     * The suffix for the table.
     *
     * @var string
     */
    protected $suffix;

    /**
     * Range start value for the partition.
     *
     * @var string
     */
    protected $start;

    /**
     * Range end value for the partition.
     *
     * @var string
     */
    protected $end;


    /**
     * Handler for the command.
     *
     */
    public function handle()
    {
        $this->suffix = $this->ask('Define your table suffix (year, month, etc...)');
        $this->start = $this->ask('Range start value');
        $this->end = $this->ask('Range end value');
        $tables = Schema::getAllRangePartitionedTables();
        foreach($tables as $table){
            $this->table = $table;
            $path = $this->getSourceFilePath();
            $contents = $this->getSourceFile();
            $this->files->put($path, $contents);
            $this->info("File : {$path} created.");

        }
    }

    /**
     * Get the full path of generate class.
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return base_path('database/migrations') . '/'.now()->format('Y_m_d_His').'_create_partition_' . $this->table.'_'.$this->suffix . '_table.php';
    }

    /**
     * Return a singular capitalized name.
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
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the stub path and the stub variables.
     *
     * @return array|false|string|string[]
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    /**
     * Replace the stub variables(key) with the desire value.
     *
     * @param $stub
     * @param array $stubVariables
     * @return array|false|string|string[]
     */
    public function getStubContents($stub, array $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Return the stub file path.
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__.'/Stubs/range-partition-migration.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value.
     *
     * @return array
     *
     */
    public function getStubVariables()
    {
        return [
            'TABLE' => $this->table,
            'SUFFIX' => $this->suffix,
            'START' => $this->start,
            'END' => $this->end
        ];
    }
}
