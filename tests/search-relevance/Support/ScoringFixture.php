<?php

namespace Lunar\Tests\SearchRelevance\Support;

use Illuminate\Support\Carbon;
use Lunar\Core\Models\Product;
use Lunar\SearchRelevance\Contracts\ScoreAggregator;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Models\SearchQueryScore;

/**
 * A fixture of logged searches and events with known expected scores, shared
 * by the PHP and SQL aggregator tests so every driver proves the same maths.
 */
final class ScoringFixture
{
    public const VERSION = 'n1:database:keyword';

    /** @return array<string, mixed> */
    public static function config(): array
    {
        return [
            'weights' => ['click' => 1, 'basket' => 3, 'purchase' => 5],
            'position_eta' => 0.7,
            'max_position_weight' => 5,
            'half_life_days' => 30,
            'window_days' => 180,
            'min_sessions' => 3,
            'max_products_per_query' => 50,
            'max_searches_per_minute' => 30,
            'keep_versions' => [self::VERSION, 'keep'],
        ];
    }

    public static function seed(): void
    {
        // Weights: clicks, baskets and purchases from three sessions each.
        foreach (['s1', 's2', 's3'] as $session) {
            $search = self::search('w', $session, [1, 2, 3]);
            self::event($search, 1, 1, 'click');
            self::event($search, 2, 1, 'purchase');
            self::event($search, 3, 1, 'basket');
        }

        // Position correction: pos 4 counts 4^0.7, pos 20 is capped at 5, explore is not corrected.
        foreach (['s1', 's2', 's3'] as $session) {
            $search = self::search('p', $session, range(1, 20));
            self::event($search, 1, 1, 'click');
            self::event($search, 2, 4, 'click');
            self::event($search, 3, 20, 'click');
            self::event($search, 4, 20, 'click', source: 'explore');
        }

        // Half-life: a click 30 days old is worth half.
        foreach (['s1', 's2', 's3'] as $session) {
            $search = self::search('d', $session, [1, 2]);
            self::event($search, 1, 1, 'click');
            self::event($search, 2, 1, 'click', at: now()->subDays(30));
        }

        // Minimum sessions: two sessions, or one session clicking five times, is not enough.
        foreach (['s1', 's2', 's3'] as $index => $session) {
            $search = self::search('m', $session, [1, 2, 3]);
            self::event($search, 2, 1, 'click');

            if ($index < 2) {
                self::event($search, 1, 1, 'click');
            }
        }
        for ($i = 0; $i < 5; $i++) {
            self::event(self::search('m', 'loner', [1, 2, 3]), 3, 1, 'click');
        }

        // Version: events logged under another retrieval version are ignored.
        foreach (['s1', 's2', 's3'] as $session) {
            $search = self::search('v', $session, [1], version: 'n0:database:keyword');
            self::event($search, 1, 1, 'click');
        }

        // Window: events older than window_days are ignored.
        foreach (['s1', 's2', 's3'] as $session) {
            $search = self::search('old', $session, [1], at: now()->subDays(181));
            self::event($search, 1, 1, 'click', at: now()->subDays(181));
        }

        // Guard: a session searching 31 times within one minute is ignored; 31 across two minutes is fine.
        $minute = now()->startOfMinute()->addSeconds(5);
        for ($i = 0; $i < 31; $i++) {
            $search = self::search('g', 'busy', [1, 2], at: $minute->copy()->addSeconds($i));
        }
        self::event($search, 1, 1, 'click');
        for ($i = 0; $i < 31; $i++) {
            $search = self::search('g', 'steady', [1, 2], at: $minute->copy()->subMinute()->addSeconds($i * 3));
        }
        self::event($search, 2, 1, 'click');
        foreach (['s1', 's2'] as $session) {
            $search = self::search('g', $session, [1, 2]);
            self::event($search, 1, 1, 'click');
            self::event($search, 2, 1, 'click');
        }

        // Stale rows: another version is swept, a kept version survives, an unrefreshed current row goes.
        SearchQueryScore::factory()->create(['normalised_query' => 'gone', 'product_id' => 1, 'version' => 'n0:database:keyword']);
        SearchQueryScore::factory()->create(['normalised_query' => 'kept', 'product_id' => 1, 'version' => 'keep']);
        SearchQueryScore::factory()->create(['normalised_query' => 'stale', 'product_id' => 1, 'version' => self::VERSION, 'updated_at' => now()->subDay()]);
    }

    public static function assertScores(ScoreAggregator $aggregator): void
    {
        $written = $aggregator->aggregate(self::VERSION, self::config());

        $rows = SearchQueryScore::query()->get()
            ->groupBy('normalised_query')
            ->map(fn ($group) => $group->keyBy('product_id'));

        expect($written)->toBe(11)
            ->and($rows->keys()->sort()->values()->all())->toBe(['d', 'g', 'kept', 'm', 'p', 'w']);

        expect($rows['w'][2]->score)->toEqualWithDelta(15.0, 0.01)
            ->and($rows['w'][3]->score)->toEqualWithDelta(9.0, 0.01)
            ->and($rows['w'][1]->score)->toEqualWithDelta(3.0, 0.01)
            ->and($rows['w'][2]->relative)->toEqualWithDelta(1.0, 0.001)
            ->and($rows['w'][3]->relative)->toEqualWithDelta(0.6, 0.001)
            ->and($rows['w'][1]->relative)->toEqualWithDelta(0.2, 0.001)
            ->and($rows['w'][1]->sessions)->toBe(3)
            ->and($rows['w'][1]->version)->toBe(self::VERSION);

        expect($rows['p'][1]->score)->toEqualWithDelta(3.0, 0.01)
            ->and($rows['p'][2]->score)->toEqualWithDelta(3 * pow(4, 0.7), 0.01)
            ->and($rows['p'][3]->score)->toEqualWithDelta(15.0, 0.01)
            ->and($rows['p'][4]->score)->toEqualWithDelta(3.0, 0.01);

        expect($rows['d'][1]->relative)->toEqualWithDelta(1.0, 0.001)
            ->and($rows['d'][2]->relative)->toEqualWithDelta(0.5, 0.001);

        expect($rows['m']->keys()->all())->toBe([2]);

        expect($rows['g']->keys()->all())->toBe([2])
            ->and($rows['g'][2]->sessions)->toBe(3);

        expect($rows->has('v'))->toBeFalse()
            ->and($rows->has('old'))->toBeFalse()
            ->and($rows->has('gone'))->toBeFalse()
            ->and($rows->has('stale'))->toBeFalse()
            ->and($rows['kept'][1]->version)->toBe('keep');
    }

    public static function search(string $query, string $session, array $shown, ?Carbon $at = null, string $version = self::VERSION): SearchQuery
    {
        return SearchQuery::factory()->create([
            'model_type' => Product::class,
            'raw_query' => $query,
            'normalised_query' => $query,
            'session_id' => $session,
            'shown' => $shown,
            'result_count' => count($shown),
            'version' => $version,
            'created_at' => $at ?? now(),
        ]);
    }

    public static function event(SearchQuery $search, int $productId, int $position, string $type, ?Carbon $at = null, string $source = 'organic'): SearchEvent
    {
        return SearchEvent::factory()->create([
            'search_id' => $search->id,
            'product_id' => $productId,
            'position' => $position,
            'type' => $type,
            'source' => $source,
            'session_id' => $search->session_id,
            'created_at' => $at ?? now(),
        ]);
    }
}
