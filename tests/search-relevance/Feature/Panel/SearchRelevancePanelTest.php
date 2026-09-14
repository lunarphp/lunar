<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\SearchRelevance\Learning\Overrides;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\RetrievalVersion;
use Lunar\SearchRelevance\Settings;
use Lunar\Tests\SearchRelevance\PanelTestCase;

uses(PanelTestCase::class);

const PERMISSION = 'search:manage-relevance';

function relevanceStaff(bool $admin = false, bool $permitted = false): Staff
{
    $staff = Staff::factory()->create(['admin' => $admin]);

    if ($permitted) {
        $staff->givePermissionTo(PERMISSION);
    }

    return $staff;
}

/**
 * Three searches for "red shoes" (one raw variant), one for "blue hat" with no
 * results, one for "green bag" with results but no clicks. Two clicks and one
 * purchase on the shoes searches.
 *
 * @return array{product: Product, searches: array<int, SearchQuery>}
 */
function relevanceFixture(): array
{
    $product = Product::factory()->create();

    $searches = SearchQuery::factory()->count(3)->create([
        'model_type' => Product::class,
        'raw_query' => 'Red Shoes',
        'normalised_query' => 'red shoes',
        'result_count' => 12,
        'shown' => [$product->id],
    ])->all();

    $searches[0]->update(['raw_query' => 'red  shoes!']);

    SearchQuery::factory()->create([
        'model_type' => Product::class,
        'raw_query' => 'blue hat',
        'normalised_query' => 'blue hat',
        'result_count' => 0,
        'shown' => [],
    ]);

    SearchQuery::factory()->create([
        'model_type' => Product::class,
        'raw_query' => 'green bag',
        'normalised_query' => 'green bag',
        'result_count' => 4,
        'shown' => [$product->id],
    ]);

    SearchEvent::factory()->create(['search_id' => $searches[0]->id, 'product_id' => $product->id, 'position' => 3, 'type' => 'click']);
    SearchEvent::factory()->create(['search_id' => $searches[1]->id, 'product_id' => $product->id, 'position' => 1, 'type' => 'click']);
    SearchEvent::factory()->create(['search_id' => $searches[1]->id, 'product_id' => $product->id, 'position' => 1, 'type' => 'basket']);
    SearchEvent::factory()->create(['search_id' => $searches[1]->id, 'product_id' => $product->id, 'position' => 1, 'type' => 'purchase']);

    return ['product' => $product, 'searches' => $searches];
}

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

it('gates every route behind the permission', function () {
    $product = Product::factory()->create();

    $this->actingAs(relevanceStaff(), 'staff');

    $this->get(route('panel.search-relevance.index'))->assertForbidden();
    $this->get(route('panel.search-relevance.query', ['query' => 'red shoes']))->assertForbidden();
    $this->getJson(route('panel.search-relevance.product', $product))->assertForbidden();
    $this->get(route('panel.settings.search-relevance.index'))->assertForbidden();
    $this->post(route('panel.settings.search-relevance.update'), ['mode' => 'off'])->assertForbidden();
});

it('allows the routes to permitted staff and admins', function () {
    $this->actingAs(relevanceStaff(permitted: true), 'staff')
        ->get(route('panel.search-relevance.index'))
        ->assertOk();

    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->get(route('panel.search-relevance.index'))
        ->assertOk();
});

it('shows the navigation only to staff with the permission', function () {
    $hasItem = fn ($groups): bool => collect($groups)->firstWhere('key', 'search') !== null
        && collect($groups)->last()['key'] === 'search'
        && collect(collect($groups)->firstWhere('key', 'search')['items'])->firstWhere('key', 'search-relevance')['icon'] === 'chart';

    $this->actingAs(relevanceStaff(permitted: true), 'staff')
        ->get('/panel')
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.groups', fn ($groups) => $hasItem($groups))
            ->where('settingsNavigation.groups', fn ($groups) => collect(collect($groups)->firstWhere('key', 'store')['items'])
                ->firstWhere('key', 'search-relevance')['url'] === route('panel.settings.search-relevance.index')));

    $this->actingAs(relevanceStaff(), 'staff')
        ->get('/panel')
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.groups', fn ($groups) => ! collect($groups)->pluck('key')->contains('search')));
});

it('renders the index page with kpis and query tables', function () {
    relevanceFixture();

    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->get(route('panel.search-relevance.index', ['range' => '7d']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('search-relevance::Index', false)
            ->where('range', '7d')
            ->where('kpis.searches', 5)
            ->where('kpis.click_through_rate', 40)
            ->where('kpis.conversion_rate', 20)
            ->where('kpis.zero_result_rate', 20)
            ->where('kpis.mean_click_position', 2)
            ->has('uplift.searches')
            ->has('uplift.mrr_shown')
            ->has('uplift.mrr_ranked')
            ->has('uplift.improved_share')
            ->has('uplift.worsened_share')
            ->has('top_queries', 3)
            ->where('top_queries.0.query', 'red shoes')
            ->where('top_queries.0.searches', 3)
            ->where('top_queries.0.clicks', 2)
            ->where('top_queries.0.conversions', 1)
            ->where('top_queries.0.conversion_rate', 33.3)
            ->where('top_queries.0.url', route('panel.search-relevance.query', ['query' => 'red shoes']))
            ->has('zero_result_queries', 1)
            ->where('zero_result_queries.0.query', 'blue hat')
            ->has('no_click_queries', 1)
            ->where('no_click_queries.0.query', 'green bag')
            ->where('urls.index', route('panel.search-relevance.index')));
});

it('renders the query page with explainability rows and raw variants', function () {
    ['product' => $product] = relevanceFixture();

    SearchQueryScore::factory()->create([
        'model_type' => Product::class,
        'normalised_query' => 'red shoes',
        'product_id' => $product->id,
        'score' => 4.2,
        'relative' => 1.0,
        'sessions' => 3,
        'version' => app(RetrievalVersion::class)->current(Product::class),
    ]);

    // A stale version is ignored.
    SearchQueryScore::factory()->create([
        'model_type' => Product::class,
        'normalised_query' => 'red shoes',
        'product_id' => Product::factory()->create()->id,
        'score' => 9,
        'relative' => 1.0,
        'sessions' => 9,
        'version' => 'stale',
    ]);

    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->get(route('panel.search-relevance.query', ['query' => 'red shoes']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('search-relevance::Query', false)
            ->where('query', 'red shoes')
            ->where('model_type', Product::class)
            ->has('learned', 1)
            ->where('learned.0.product_id', $product->id)
            ->where('learned.0.name', (string) $product->translate('name'))
            ->where('learned.0.relative', 1)
            ->where('learned.0.score', 4.2)
            ->where('learned.0.clicks', 2)
            ->where('learned.0.baskets', 1)
            ->where('learned.0.purchases', 1)
            ->where('learned.0.sessions', 3)
            ->where('learned.0.typical_position', 1.5)
            ->whereNot('learned.0.last_event_at', null)
            ->where('learned.0.url', route('panel.products.edit', $product))
            ->has('variants', 2)
            ->where('variants.0.raw_query', 'Red Shoes')
            ->where('variants.0.searches', 2)
            ->where('variants.1.raw_query', 'red  shoes!'));
});

it('returns the product search performance as json', function () {
    ['product' => $product] = relevanceFixture();

    SearchQueryScore::factory()->create([
        'model_type' => Product::class,
        'normalised_query' => 'red shoes',
        'product_id' => $product->id,
        'score' => 4.2,
        'relative' => 0.75,
        'sessions' => 3,
        'version' => app(RetrievalVersion::class)->current(Product::class),
    ]);

    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->getJson(route('panel.search-relevance.product', $product))
        ->assertOk()
        ->assertJsonCount(1, 'queries')
        ->assertJsonPath('queries.0.query', 'red shoes')
        ->assertJsonPath('queries.0.relative', 0.75)
        ->assertJsonPath('queries.0.clicks', 2)
        ->assertJsonPath('queries.0.baskets', 1)
        ->assertJsonPath('queries.0.purchases', 1)
        ->assertJsonPath('queries.0.url', route('panel.search-relevance.query', ['query' => 'red shoes']));
});

it('shares the product slot entry on the product edit page', function () {
    $product = Product::factory()->create();

    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->get(route('panel.products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', fn ($slots) => collect($slots->get('products.edit:content:after'))
                ->contains(fn ($entry) => $entry['component'] === 'search-relevance::ProductSearchPerformance')));
});

it('renders the settings page', function () {
    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->get(route('panel.settings.search-relevance.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('search-relevance::Settings/Index', false)
            ->where('mode', app(Settings::class)->mode())
            ->where('modes', ['off', 'shadow', 'on'])
            ->where('weights', config('lunar.search_relevance.scoring.weights'))
            ->where('versions.0.model', Product::class)
            ->where('versions.0.label', 'Product')
            ->where('versions.0.version', app(RetrievalVersion::class)->current(Product::class))
            ->where('urls.update', route('panel.settings.search-relevance.update')));
});

it('persists the mode from the settings update', function () {
    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->from(route('panel.settings.search-relevance.index'))
        ->post(route('panel.settings.search-relevance.update'), ['mode' => 'on'])
        ->assertRedirect(route('panel.settings.search-relevance.index'))
        ->assertSessionHas('success');

    expect(app()->make(Settings::class)->mode())->toBe('on');

    $this->get(route('panel.settings.search-relevance.index'))
        ->assertInertia(fn (Assert $page) => $page->where('mode', 'on'));
});

it('rejects an invalid mode', function () {
    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->from(route('panel.settings.search-relevance.index'))
        ->post(route('panel.settings.search-relevance.update'), ['mode' => 'loud'])
        ->assertRedirect(route('panel.settings.search-relevance.index'))
        ->assertSessionHasErrors('mode');
});

it('contributes the search conversion dashboard widget with deferred data', function () {
    relevanceFixture();

    $this->actingAs(relevanceStaff(admin: true), 'staff')
        ->get(route('panel.dashboard', ['range' => '7d']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('widgets', fn ($widgets) => collect($widgets)->firstWhere('key', 'search-relevance-conversion')['component']
                === 'search-relevance::SearchConversionWidget')
            ->loadDeferredProps(fn (Assert $props) => $props
                ->where('widgetData.search-relevance-conversion.searches', 5)
                ->where('widgetData.search-relevance-conversion.conversion_rate', 20)
                ->where('widgetData.search-relevance-conversion.previous_searches', 0)
                ->where('widgetData.search-relevance-conversion.searches_delta.value', 'new')));
});

it('hides the dashboard widget from staff without the permission', function () {
    $this->actingAs(relevanceStaff(), 'staff')
        ->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('widgets', fn ($widgets) => ! collect($widgets)->pluck('key')->contains('search-relevance-conversion')));
});

it('finds normalised queries through the global search', function () {
    relevanceFixture();

    $rows = collect(
        $this->actingAs(relevanceStaff(admin: true), 'staff')->getJson('/panel/search?q=shoes')->assertOk()->json('data')
    );

    $row = $rows->firstWhere('kind', 'search-queries');

    expect($rows->where('kind', 'search-queries'))->toHaveCount(1)
        ->and($row['id'])->toBe('red shoes')
        ->and($row['label'])->toBe('red shoes')
        ->and($row['hint'])->toBe('3 searches')
        ->and($row['url'])->toBe(route('panel.search-relevance.query', ['query' => 'red shoes']));

    $hidden = collect(
        $this->actingAs(relevanceStaff(), 'staff')->getJson('/panel/search?q=shoes')->assertOk()->json('data')
    );

    expect($hidden->where('kind', 'search-queries'))->toBeEmpty();
});

it('serves the add-on lang group from the translations endpoint', function () {
    $this->getJson('/panel/translations/en')
        ->assertOk()
        ->assertJsonPath('messages.search-relevance::panel.title', 'Search relevance');
});

it('lets staff exclude a product from learning and allow it again', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $product = Product::factory()->create();
    SearchQueryScore::factory()->create(['normalised_query' => 'hoodie', 'product_id' => $product->id, 'version' => app(RetrievalVersion::class)->current(Product::class)]);

    $this->actingAs($staff, 'staff')
        ->from(route('panel.search-relevance.query', ['query' => 'hoodie']))
        ->post(route('panel.search-relevance.exclude', ['query' => 'hoodie', 'productId' => $product->id]))
        ->assertRedirect(route('panel.search-relevance.query', ['query' => 'hoodie']))
        ->assertSessionHas('success');

    expect(SearchQueryScore::query()->count())->toBe(0)
        ->and(app(Overrides::class)->excluded(Product::class, 'hoodie')->all())->toBe([$product->id]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.search-relevance.query', ['query' => 'hoodie']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('search-relevance::Query', false)
            ->has('excluded', 1)
            ->where('excluded.0.product_id', $product->id)
            ->where('reset_at', null)
            ->has('urls.reset'));

    $this->actingAs($staff, 'staff')
        ->delete(route('panel.search-relevance.include', ['query' => 'hoodie', 'productId' => $product->id]))
        ->assertRedirect();

    expect(app(Overrides::class)->excluded(Product::class, 'hoodie')->isEmpty())->toBeTrue();
});

it('lets staff reset learning for a query', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    SearchQueryScore::factory()->create(['normalised_query' => 'hoodie', 'product_id' => 1, 'version' => app(RetrievalVersion::class)->current(Product::class)]);
    SearchQueryScore::factory()->create(['normalised_query' => 'mug', 'product_id' => 1, 'version' => app(RetrievalVersion::class)->current(Product::class)]);

    $this->actingAs($staff, 'staff')
        ->post(route('panel.search-relevance.reset', ['query' => 'hoodie']))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(SearchQueryScore::query()->pluck('normalised_query')->all())->toBe(['mug'])
        ->and(app(Overrides::class)->resetAt(Product::class, 'hoodie'))->not->toBeNull();

    $this->actingAs($staff, 'staff')
        ->get(route('panel.search-relevance.query', ['query' => 'hoodie']))
        ->assertInertia(fn (Assert $page) => $page->where('reset_at', fn ($value) => $value !== null));
});

it('gates the override routes behind the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff')
        ->post(route('panel.search-relevance.reset', ['query' => 'hoodie']))
        ->assertForbidden();
});
