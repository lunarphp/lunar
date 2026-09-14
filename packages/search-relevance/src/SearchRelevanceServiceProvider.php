<?php

namespace Lunar\SearchRelevance;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Models\CartLine;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\PanelManager;
use Lunar\SearchRelevance\Console\PruneCommand;
use Lunar\SearchRelevance\Console\ReplayCommand;
use Lunar\SearchRelevance\Console\ScoreCommand;
use Lunar\SearchRelevance\Contracts\QueryNormaliser;
use Lunar\SearchRelevance\Contracts\Ranker;
use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Listeners\AttributeCartLine;
use Lunar\SearchRelevance\Listeners\AttributeOrderLines;
use Lunar\SearchRelevance\Panel\SearchRelevanceSection;
use Lunar\SearchRelevance\Pipelines\PartNumberRetrieval;
use Lunar\SearchRelevance\Pipelines\RankResults;
use Lunar\SearchRelevance\Pipelines\WidenRequest;
use Lunar\SearchRelevance\Scoring\MySqlScoreAggregator;
use Lunar\SearchRelevance\Scoring\PhpScoreAggregator;
use Lunar\SearchRelevance\Scoring\PostgresScoreAggregator;
use Lunar\SearchRelevance\Signals\SignalCombiner;

class SearchRelevanceServiceProvider extends ServiceProvider
{
    protected string $root = __DIR__.'/..';

    public function register(): void
    {
        $this->mergeConfigFrom("{$this->root}/config/search-relevance.php", 'lunar.search_relevance');

        // Holds a per-request memo of the persisted mode; scoped, not singleton.
        $this->app->scoped(Settings::class);

        $this->app->bind(QueryNormaliser::class, fn ($app) => $app->make(config('lunar.search_relevance.normaliser')));

        $this->app->bind(SignalCombiner::class, fn ($app) => new SignalCombiner(
            collect(config('lunar.search_relevance.signals', []))
                ->mapWithKeys(fn (float $weight, string $class) => [$class => $weight])
                ->all(),
            $app,
        ));

        $this->app->bind(Ranker::class, fn ($app) => $app->make(config('lunar.search_relevance.ranker')));

        $this->app->bind(ScoreAggregator::class, function ($app) {
            return match ($app['db']->connection(config('lunar.database.connection'))->getDriverName()) {
                'mysql', 'mariadb' => $app->make(MySqlScoreAggregator::class),
                'pgsql' => $app->make(PostgresScoreAggregator::class),
                default => $app->make(PhpScoreAggregator::class),
            };
        });
    }

    public function boot(): void
    {
        $this->publishes([
            "{$this->root}/config/search-relevance.php" => config_path('lunar/search-relevance.php'),
        ], 'lunar.search-relevance.config');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom("{$this->root}/database/migrations");
        }

        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'search-relevance');
        $this->loadRoutesFrom("{$this->root}/routes/storefront.php");

        Blade::anonymousComponentPath("{$this->root}/resources/views/components", 'lunar-search-relevance');

        $this->registerPipelines();
        $this->registerListeners();
        $this->registerConsole();
        $this->registerPanel();
    }

    /**
     * Append the package stages to the search pipelines. Hosts that set the
     * pipelines explicitly in their own config keep full control of the order.
     */
    protected function registerPipelines(): void
    {
        $request = config('lunar.search.pipelines.request', []);
        $results = config('lunar.search.pipelines.results', []);

        foreach ([PartNumberRetrieval::class, WidenRequest::class] as $stage) {
            if (! in_array($stage, $request, true)) {
                $request[] = $stage;
            }
        }

        if (! in_array(RankResults::class, $results, true)) {
            $results[] = RankResults::class;
        }

        config([
            'lunar.search.pipelines.request' => $request,
            'lunar.search.pipelines.results' => $results,
        ]);
    }

    protected function registerListeners(): void
    {
        CartLine::observe(AttributeCartLine::class);
        Event::listen(OrderPlaced::class, AttributeOrderLines::class);
    }

    protected function registerConsole(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ScoreCommand::class,
            ReplayCommand::class,
            PruneCommand::class,
        ]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('lunar:search-relevance:score')
                ->dailyAt(config('lunar.search_relevance.scoring.schedule', '02:00'))
                ->withoutOverlapping();

            $schedule->command('lunar:search-relevance:prune')->weekly();
        });
    }

    /** The panel section only exists when lunarphp/panel is installed and booted. */
    protected function registerPanel(): void
    {
        if (! class_exists(PanelManager::class) || ! $this->app->bound(PanelManager::class)) {
            return;
        }

        Panel::section(new SearchRelevanceSection);

        $this->app->make(PanelManager::class)->vite('search-relevance', [
            'input' => 'resources/js/addon.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/search-relevance',
            '__buildSourcePath' => "{$this->root}/build",
        ]);
    }
}
