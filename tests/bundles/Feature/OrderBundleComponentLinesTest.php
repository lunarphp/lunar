<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
    $this->cart = Catalogue::checkoutCart($this->currency);

    $this->bundleVariant = Catalogue::variant($this->currency, 5000);
    $this->body = Catalogue::variant($this->currency, 3000, stock: 10, attributes: ['selling_policy' => SellingPolicy::InStock]);
    $this->bag = Catalogue::variant($this->currency, 500, stock: 20, attributes: ['selling_policy' => SellingPolicy::InStock]);

    $this->bundle = app(DefinesBundle::class)->execute($this->bundleVariant, BundlePricing::Fixed);
    $this->bundle->syncComponents([
        ['variant' => $this->body, 'quantity' => 1],
        ['variant' => $this->bag, 'quantity' => 3],
    ]);
});

function placeOrder(Order $order): Order
{
    $order->update(['placed_at' => now()]);

    return $order->refresh();
}

test('a bundle line gains component lines, a fulfilment and committed component stock', function () {
    $cart = $this->cart->add($this->bundleVariant, 2);
    $cartLine = $cart->lines->first();

    $order = placeOrder($cart->createOrder());

    $parent = $order->lines()->topLevel()->where('purchasable_id', $this->bundleVariant->id)->first();

    expect($parent->type)->toBe('bundle')
        ->and($parent->requires_fulfilment)->toBeFalse()
        ->and($parent->requires_shipping)->toBeTrue()
        ->and($parent->quantity)->toBe(2)
        ->and($parent->unit_price)->toBe(5000)
        ->and($parent->sub_total)->toBe($cartLine->subTotal->value)
        ->and($parent->total)->toBe($cartLine->total->value)
        ->and($parent->refundableQuantity())->toBe(2);

    $components = $parent->components()->get();

    expect($components)->toHaveCount(2)
        ->and($components->every(fn (OrderLine $line) => $line->parent_line_id === $parent->id))->toBeTrue()
        ->and($components->pluck('quantity')->all())->toBe([2, 6])
        ->and($components->pluck('purchasable_id')->all())->toBe([$this->body->id, $this->bag->id])
        ->and($components->pluck('type')->unique()->all())->toBe(['physical'])
        ->and($components->every(fn (OrderLine $line) => $line->requires_fulfilment && $line->requires_shipping))->toBeTrue()
        ->and($components->every(fn (OrderLine $line) => $line->unit_price === 0 && $line->sub_total === 0 && $line->tax_total === 0 && $line->total === 0))->toBeTrue()
        ->and($components->first()->tax_breakdown->amounts)->toBeEmpty()
        ->and($components->first()->identifier)->toBe($this->body->sku)
        ->and($components->first()->refundableQuantity())->toBe(0)
        ->and($components->sum(fn (OrderLine $line) => $line->meta['allocated_total']))->toBe($parent->total)
        // Weighted 2:1 by current unit price times quantity (3000 x 2 against 500 x 6).
        ->and($components->first()->meta['allocated_total'])->toBe(2 * $components->last()->meta['allocated_total']);

    expect($order->lines()->topLevel()->count())->toBe(2)
        ->and($order->lines()->count())->toBe(4);

    $fulfilment = $order->fulfilments()->first();

    expect($order->fulfilments)->toHaveCount(1)
        ->and($fulfilment->lines->pluck('order_line_id')->sort()->values()->all())->toBe($components->pluck('id')->sort()->values()->all())
        ->and($fulfilment->lines->pluck('quantity')->sum())->toBe(8);

    expect($this->body->fresh()->stock_committed)->toBe(2)
        ->and($this->bag->fresh()->stock_committed)->toBe(6)
        ->and($this->bundleVariant->fresh()->stock_committed)->toBe(0);
});

test('shipping the fulfilment releases the component commitment', function () {
    $order = placeOrder($this->cart->add($this->bundleVariant, 1)->createOrder());

    $order->fulfilments()->first()->ship();

    expect($this->body->fresh())->stock_committed->toBe(0)->stock_on_hand->toBe(9)
        ->and($this->bag->fresh())->stock_committed->toBe(0)->stock_on_hand->toBe(17);
});

test('re-running creation for a draft order replaces the component lines', function () {
    $cart = $this->cart->add($this->bundleVariant, 2);
    $order = $cart->createOrder();

    $before = OrderLine::query()->whereNotNull('parent_line_id')->pluck('id');

    $again = $cart->createOrder(orderIdToUpdate: $order->id);

    $components = OrderLine::query()->whereNotNull('parent_line_id')->get();

    expect($again->id)->toBe($order->id)
        ->and($components)->toHaveCount(2)
        ->and($components->pluck('id')->intersect($before))->toBeEmpty()
        ->and($components->pluck('quantity')->all())->toBe([2, 6])
        ->and($again->lines()->topLevel()->where('type', 'bundle')->count())->toBe(1)
        ->and($again->lines()->count())->toBe(4);
});

test('a mixed physical and digital bundle fulfils the physical part and records the digital one', function () {
    $key = Catalogue::variant($this->currency, 900, stock: 5, attributes: ['shippable' => false, 'selling_policy' => SellingPolicy::InStock]);
    $this->bundle->syncComponents([
        ['variant' => $this->body, 'quantity' => 1],
        ['variant' => $key, 'quantity' => 1],
    ]);

    $order = placeOrder($this->cart->add($this->bundleVariant, 1)->createOrder());

    $parent = $order->lines()->topLevel()->where('type', 'bundle')->first();
    $components = $parent->components()->get();
    $digital = $components->firstWhere('purchasable_id', $key->id);

    expect($components)->toHaveCount(2)
        ->and($digital->type)->toBe('digital')
        ->and($digital->requires_shipping)->toBeFalse()
        ->and($digital->requires_fulfilment)->toBeFalse()
        ->and($order->fulfilments)->toHaveCount(1)
        ->and($order->fulfilments->first()->lines->pluck('order_line_id')->all())->toBe([$components->firstWhere('purchasable_id', $this->body->id)->id])
        ->and($this->body->fresh()->stock_committed)->toBe(1)
        ->and($key->fresh()->stock_committed)->toBe(0);
});

test('a selected configuration is recorded as sold', function () {
    $lens = $this->bundle->syncGroups([['name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 1]])->groups->first();
    $lensA = Catalogue::variant($this->currency, 1000, stock: 5, attributes: ['selling_policy' => SellingPolicy::InStock]);
    $lensB = Catalogue::variant($this->currency, 2000, stock: 5, attributes: ['selling_policy' => SellingPolicy::InStock]);
    $this->bundle->syncComponents([
        ['variant' => $this->body, 'quantity' => 1],
        ['variant' => $lensA, 'quantity' => 1, 'group' => $lens, 'default' => true],
        ['variant' => $lensB, 'quantity' => 1, 'group' => $lens],
    ]);
    $chosen = $this->bundle->components->firstWhere('product_variant_id', $lensB->id);

    $order = placeOrder($this->cart->add($this->bundleVariant, 1, ['bundle' => ['selections' => [$lens->public_id => [$chosen->public_id]]]])->createOrder());

    $parent = $order->lines()->topLevel()->where('type', 'bundle')->first();

    expect($parent->meta['bundle']['selections'][$lens->public_id])->toBe([$chosen->public_id])
        ->and($parent->components()->pluck('purchasable_id')->all())->toBe([$this->body->id, $lensB->id])
        ->and($lensB->fresh()->stock_committed)->toBe(1)
        ->and($lensA->fresh()->stock_committed)->toBe(0);
});
