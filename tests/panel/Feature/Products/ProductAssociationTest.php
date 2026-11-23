<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

enum CustomAssociationType: string implements ProvidesProductAssociationType
{
    case BUNDLE = 'bundle';
    case CROSS_SELL = 'cross-sell';

    public function label(): string
    {
        return match ($this) {
            self::BUNDLE => 'Bundle',
            self::CROSS_SELL => 'Cross Sell',
        };
    }
}

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $this->product->id]);

    [$this->a, $this->b, $this->c] = Product::factory()->count(3)->create()->all();
});

/** @return array<int, int> */
function linkedIds(Product $product, string $type): array
{
    return $product->associations()->where('type', $type)->pluck('product_target_id')->map(fn ($id) => (int) $id)->all();
}

it('seeds each association type as an ordered id list with display data for the targets', function () {
    $target = Product::factory()->create(['name' => collect(['en' => 'Charger'])]);
    $this->product->syncAssociations('cross-sell', [$target->id, $this->a->id]);

    // Types follow the enum: cross-sell, up-sell, alternate.
    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('associationTypes.0.type', 'cross-sell')
            ->where('associationTypes.2.type', 'alternate')
            ->where('formSliceValues.association:cross-sell', [$target->id, $this->a->id])
            ->where('formSliceValues.association:alternate', [])
            ->where("associationProducts.{$target->id}.name", 'Charger')
        );
});

it('drafts association changes without touching the product until commit', function () {
    $this->product->syncAssociations('cross-sell', [$this->a->id, $this->b->id]);
    $keptRow = $this->product->associations()->where('product_target_id', $this->b->id)->value('id');

    // Reorder, unlink one and link another, all in one drafted value.
    $data = ['association:cross-sell' => [$this->c->id, $this->b->id]];

    $this->patchJson(route('panel.products.draft.update', $this->product), ['data' => $data])->assertOk();

    expect(linkedIds($this->product, 'cross-sell'))->toBe([$this->a->id, $this->b->id])
        ->and(EditDraft::sole()->data)->toBe($data);

    $this->postJson(route('panel.products.draft.commit', $this->product), ['data' => $data, 'rebase' => []])
        ->assertOk();

    expect(linkedIds($this->product, 'cross-sell'))->toBe([$this->c->id, $this->b->id])
        ->and($this->product->associations()->where('product_target_id', $this->b->id)->value('id'))->toBe($keptRow)
        ->and(EditDraft::count())->toBe(0);
});

it('drops duplicate ids when drafting', function () {
    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['association:up-sell' => [$this->b->id, $this->a->id, $this->b->id]],
    ])->assertOk();

    expect(EditDraft::sole()->data['association:up-sell'])->toBe([$this->b->id, $this->a->id]);
});

it('refuses to link the product to itself or to a product that does not exist', function () {
    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['association:cross-sell' => [$this->a->id, $this->product->id, 999999]],
        'rebase' => [],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['association:cross-sell.1', 'association:cross-sell.2']);

    expect(linkedIds($this->product, 'cross-sell'))->toBe([]);
});

it('refuses an association type that is not registered', function () {
    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['association:bff' => [$this->a->id]],
    ])->assertUnprocessable();
});

it('drafts and commits a registered custom association type', function () {
    config()->set('lunar.products.association_types_enum', CustomAssociationType::class);

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('associationTypes.0.type', 'bundle')
            ->where('associationTypes.0.label', 'Bundle')
            ->has('associationTypes', 2)
        );

    $data = ['association:bundle' => [$this->a->id]];

    $this->postJson(route('panel.products.draft.commit', $this->product), ['data' => $data, 'rebase' => []])
        ->assertOk();

    expect(linkedIds($this->product, 'bundle'))->toBe([$this->a->id]);
});

it('conflicts only on the association type a colleague changed', function () {
    $this->product->syncAssociations('cross-sell', [$this->a->id]);

    $mine = [
        'association:cross-sell' => [$this->a->id, $this->b->id],
        'association:up-sell' => [$this->c->id],
    ];
    $base = ['association:cross-sell' => [$this->a->id], 'association:up-sell' => []];

    $this->patchJson(route('panel.products.draft.update', $this->product), ['data' => $mine, 'base' => $base])
        ->assertOk();

    // A colleague relinks cross-sells in the meantime.
    $this->product->syncAssociations('cross-sell', [$this->c->id]);

    $response = $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => $mine,
        'base' => $base,
        'rebase' => [],
    ])->assertConflict();

    expect(collect($response->json('conflicts'))->pluck('key')->all())->toBe(['association:cross-sell'])
        ->and(linkedIds($this->product, 'up-sell'))->toBe([]);
});

it('serves display data for products only a restored draft links', function () {
    $drafted = Product::factory()->create(['name' => collect(['en' => 'Drafted link'])]);

    $this->patchJson(route('panel.products.draft.update', $this->product), [
        'data' => ['association:alternate' => [$drafted->id]],
    ])->assertOk();

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where("associationProducts.{$drafted->id}.name", 'Drafted link')
        );
});

it('exposes the cheapest default-currency price for each linked product', function () {
    $currency = Currency::factory()->create(['default' => true, 'enabled' => true, 'decimal_places' => 2]);

    $target = Product::factory()->create(['name' => collect(['en' => 'Charger'])]);
    $cheap = ProductVariant::factory()->create(['product_id' => $target->id]);
    $dear = ProductVariant::factory()->create(['product_id' => $target->id]);

    $cheapPrice = Price::factory()->create([
        'priceable_type' => $cheap->getMorphClass(), 'priceable_id' => $cheap->id,
        'currency_id' => $currency->id, 'customer_group_id' => null, 'min_quantity' => 1, 'price' => 79900,
    ]);
    Price::factory()->create([
        'priceable_type' => $dear->getMorphClass(), 'priceable_id' => $dear->id,
        'currency_id' => $currency->id, 'customer_group_id' => null, 'min_quantity' => 1, 'price' => 99900,
    ]);

    $this->product->syncAssociations('cross-sell', [$target->id]);

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where("associationProducts.{$target->id}.price", $cheapPrice->format('price'))
            ->where("associationProducts.{$target->id}.variants_count", 2)
        );
});

it('no longer exposes immediate association endpoints', function () {
    expect(Route::has('panel.products.associations.store'))->toBeFalse()
        ->and(Route::has('panel.products.associations.reorder'))->toBeFalse()
        ->and(Route::has('panel.products.associations.destroy'))->toBeFalse();
});
