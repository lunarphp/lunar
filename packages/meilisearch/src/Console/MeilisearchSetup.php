<?php

namespace Lunar\Meilisearch\Console;

use Illuminate\Console\Command;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeiliSearchEngine;
use MeiliSearch\Exceptions\ApiException;

class MeilisearchSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:meilisearch:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up filterable and sortable attributes to Meilisearch';

    /**
     * Meilisearch engine
     */
    protected MeiliSearchEngine $engine;

    /**
     * Execute the console command.
     */
    public function handle(EngineManager $engine): void
    {
        // Return the models we want to search on.
        $searchables = config('lunar.search.models', []);

        $this->engine = $engine->createMeilisearchDriver();

        // Make sure we have the relevant indexes ready to go.
        foreach ($searchables as $searchable) {
            $model = (new $searchable());

            $indexName = $model->searchableAs();

            try {
                $index = $this->engine->getIndex($indexName);
                $this->warn("Index {$indexName} found for {$searchable}");
            } catch (ApiException $e) {
                $this->warn($e->getMessage());
                $this->info("Creating index {$indexName} for {$searchable}");

                $task = $this->engine->createIndex($indexName);
                $this->engine->waitForTask($task['taskUid']);

                $index = $this->engine->getIndex($indexName);
            }

            $this->info("Update filterable fields to {$searchable}");
            $task = $index->updateFilterableAttributes(
                $model->getFilterableAttributes()
            );
            $this->engine->waitForTask($task['taskUid']);

            $this->info("Update sortable fields to {$searchable}");
            $task = $index->updateSortableAttributes(
                $model->getSortableAttributes()
            );
            $this->engine->waitForTask($task['taskUid']);

            $this->newLine();
        }
    }
}
