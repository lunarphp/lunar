<?php

namespace GetCandy\Console\Commands;

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
    protected $signature = 'getcandy:meilisearch:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up Meilisearch';

    protected MeiliSearchEngine $engine;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(EngineManager $engine)
    {
        // Return the models we want to search on.
        $searchables = config('getcandy.search.models', []);

        $this->engine = $engine->createMeilisearchDriver();

        // Make sure we have the relevant indexes ready to go.
        foreach ($searchables as $searchable) {
        // foreach ($this->searchables as $searchable) {
            $model = (new $searchable());

            $indexName = $model->searchableAs();

            try {
                $index = $this->engine->getIndex($indexName);
                $this->warn("Index {$indexName} found for {$searchable}");
            } catch (ApiException $e) {
                $this->warn($e->getMessage());
                $this->info("Creating index {$indexName} for {$searchable}");
                $this->engine->createIndex($indexName);
                sleep(1);
                $index = $this->engine->getIndex($indexName);
            }

            $this->info("Adding filterable fields to {$searchable}");

            $index->updateFilterableAttributes(
                $model->getFilterableAttributes()
            );

            $this->info("Adding sortable fields to {$searchable}");

            $index->updateSortableAttributes(
                $model->getSortableAttributes()
            );

            $this->newLine();
        }
    }
}
