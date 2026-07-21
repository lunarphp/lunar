<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
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
});

it('links picked products with the requested type', function () {
    $targets = Product::factory()->count(2)->create();

    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'cross-sell',
        'product_ids' => $targets->pluck('id')->all(),
    ])->assertRedirect();

    expect($this->product->associations()->where('type', 'cross-sell')->count())->toBe(2);
});

it('skips already-linked targets and the product itself', function () {
    $target = Product::factory()->create();
    $this->product->associations()->create(['product_target_id' => $target->id, 'type' => 'up-sell']);

    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'up-sell',
        'product_ids' => [$target->id, $this->product->id],
    ])->assertRedirect();

    expect($this->product->associations()->where('type', 'up-sell')->count())->toBe(1);
});

it('rejects unknown association types', function () {
    $target = Product::factory()->create();

    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'bff',
        'product_ids' => [$target->id],
    ])->assertSessionHasErrors('type');
});

it('removes a link and serves the grouped payload on the edit page', function () {
    $target = Product::factory()->create(['name' => collect(['en' => 'Charger'])]);
    $association = $this->product->associations()->create([
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    // Groups are ordered by the enum: cross-sell, up-sell, alternate.
    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('associations.0.type', 'cross-sell')
            ->where('associations.0.entries.0.name', 'Charger')
            ->where('associations.2.type', 'alternate')
            ->has('associations.2.entries', 0)
        );

    $this->delete(route('panel.products.associations.destroy', [$this->product, $association]))
        ->assertRedirect();

    expect($this->product->associations()->count())->toBe(0);
});

it('renders and accepts a registered custom association type', function () {
    config()->set('lunar.products.association_types_enum', CustomAssociationType::class);

    $target = Product::factory()->create();

    // The custom type leads the group list and carries its enum label.
    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('associations.0.type', 'bundle')
            ->where('associations.0.label', 'Bundle')
            ->has('associations', 2)
        );

    // Validation accepts the custom type, and the link is stored.
    $this->post(route('panel.products.associations.store', $this->product), [
        'type' => 'bundle',
        'product_ids' => [$target->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($this->product->associations()->where('type', 'bundle')->count())->toBe(1);
});

it('scopes association routes to the owning product', function () {
    $target = Product::factory()->create();
    $other = Product::factory()->create();
    $association = $other->associations()->create([
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    $this->delete(route('panel.products.associations.destroy', [$this->product, $association]))
        ->assertNotFound();
});
