<?php

namespace GetCandy\Console\Commands;

use GetCandy\Addons\Manifest;
use GetCandy\Models\Order;
use Illuminate\Console\Command;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeiliSearchEngine;

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
        $this->engine = $engine->createMeilisearchDriver();

        foreach ($this->engine->getAllIndexes() as $index) {
            // Set up soft deletes.
            $index->updateFilterableAttributes([
                '__soft_deleted',
            ]);
        }

        $this->setUpOrders();
    }

    protected function setUpOrders()
    {
        $index = $this->engine->getIndex(
            (new Order)->searchableAs()
        );

        $index->updateFilterableAttributes([
            'status',
            'placed_at',
            'created_at',
            'total',
        ]);
    }
}
