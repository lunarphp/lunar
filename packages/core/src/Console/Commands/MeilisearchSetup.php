<?php

namespace GetCandy\Console\Commands;

use GetCandy\Models\Customer;
use GetCandy\Models\Order;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
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
     * The models we want to search on.
     *
     * @var array
     */
    protected $searchables = [
        Product::class,
        Order::class,
        ProductOption::class,
        Customer::class,
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(EngineManager $engine)
    {
        $this->engine = $engine->createMeilisearchDriver();

        // Make sure we have the relevant indexes ready to go.
        foreach ($this->searchables as $searchable) {
            $indexName = (new $searchable())->searchableAs();

            try {
                $index = $this->engine->getIndex($indexName);
            } catch (ApiException $e) {
                $this->info("Creating index for {$searchable}");
                $this->engine->createIndex($indexName);

                $index = $this->engine->getIndex($indexName);
            }

            $index->updateFilterableAttributes([
                '__soft_deleted',
            ]);
        }

        $this->setUpOrders();
    }

    protected function setUpOrders()
    {
        $index = $this->engine->getIndex(
            (new Order())->searchableAs()
        );

        $index->updateFilterableAttributes([
            'status',
            'placed_at',
            'created_at',
            'total',
        ]);

        $index->updateSortableAttributes([
            'reference',
            'status',
            'placed_at',
            'created_at',
            'total',
        ]);
    }
}
