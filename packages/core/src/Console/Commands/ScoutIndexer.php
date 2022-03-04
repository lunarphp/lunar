<?php

namespace GetCandy\Console\Commands;

use Illuminate\Console\Command;
use GetCandy\Models\Collection;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Models\Customer;
use GetCandy\Models\Order;

class ScoutIndexer extends Command
{
    /**
     * GetCandy models for indexing
     */
    private $models = [
        Collection::class,
        Product::class,
        ProductOption::class,
        Customer::class,
        Order::class,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getcandy:indexer 
                            {models?* : Model or space-separated list of Models for indexing.}
                            {--ignore : If informed, only uses the models listed in the command call for indexing, ignoring the Models present in the command class.}
                            {--flush : If informed, it does not index the records, it just deletes them.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete and index model data through Laravel Scout';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Executes the re-index of the informed models
     * @param array $models Models array for indexing
     * @return void
     */
    private function indexer (array $models) :void {
        foreach($models as $model) {
            $this->newLine();

            // Delete model records from the index
            $this->warn('Deleting [' . $model . '] records from the index.');
            $this->call('scout:flush', ['model' => $model]);
            // Checks whether to import the records
            if (! $this->option('flush')) {
                // Import model records to index
                $this->call('scout:import', ['model' => $model]);
            }

            $this->newLine();
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Checks whether to ignore models pinned to the class
        if ($this->option('ignore')) {
            // Checks if a model was passed in the call
            if (empty($this->argument('models'))) {
                // Error if option [--ignore] is passed and no model is informed
                $this->newLine();
                $this->error('No model passed on call');
                $this->info('When using the [--ignore] argument, you must provide at least one model to index.');
                $this->newLine();
                return;
            } else {
                // Run the indexer commands
                $this->indexer($this->argument('models'));    
            }
        } else {
            // Returns only the models of the call that are not present in the class array
            $diff = array_diff($this->argument('models'), $this->models);
            // Merge call models with class models
            $models = array_merge($diff, $this->models);
            // Run the indexer commands
            $this->indexer($models);
        }

        return 0;
    }
}
