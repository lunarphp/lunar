<?php

namespace Lunar\Console\Commands;

use Illuminate\Console\Command;

class ScoutIndexerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:search:index
                            {models?* : Model or space-separated list of Models for indexing.}
                            {--ignore : If informed, only uses the models listed in the command call for indexing, ignoring the Models present in the config file.}
                            {--refresh : If informed, the records will be delete before indexing. Can\'t be used with the [--flush] option.}
                            {--flush : If informed, does not index the records, just deletes them. Can\'t be used with the [--refresh] option.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index model data through Laravel Scout';

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
     * Executes the re-index of the informed models.
     *
     * @param  array  $models  Models array for indexing
     */
    private function indexer(array $models): void
    {
        foreach ($models as $model) {
            // Check whether to delete the records
            if ($this->option('flush') || $this->option('refresh')) {
                // Delete model records from the index
                $this->components->warn('Deleting ['.$model.'] records from the index.');
                $this->call('scout:flush', ['model' => $model]);
            }

            // Checks whether to import the records
            if (! $this->option('flush')) {
                // Import model records to index
                $this->call('scout:import', ['model' => $model]);
            }
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if --refresh and --flush options has been passed
        if ($this->option('flush') && $this->option('refresh')) {
            $this->components->error('You can\'t use the [--refresh] and [--flush] options together.');

            return static::FAILURE;
        }

        // Return searchable models from config
        $searchables = config('lunar.search.models', []);

        // Checks whether to ignore models pinned to the class
        if ($this->option('ignore')) {
            // Checks if a model was passed in the call
            if (empty($this->argument('models'))) {
                // Error if option [--ignore] is passed and no model is informed
                $this->components->error('No model passed on call');
                $this->components->info('When using the [--ignore] option, you must provide at least one model to index.');

                return static::FAILURE;
            } else {
                // Run the indexer commands
                $this->indexer($this->argument('models'));
            }
        } else {
            // Returns only the models of the call that are not present in the class array
            $diff = array_diff($this->argument('models'), $searchables);
            // Merge call models with class models
            $models = array_merge($diff, $searchables);
            // Run the indexer commands
            $this->indexer($models);
        }

        return Command::SUCCESS;
    }
}
