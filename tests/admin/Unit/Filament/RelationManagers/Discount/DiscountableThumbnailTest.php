<?php

use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Filament\RelationManagers\Discount\ProductConditionRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductRewardRelationManager;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    Language::factory()->create(['default' => true]);

    $this->asStaff(admin: true);
});

function discountWithProduct(string $type, bool $withMedia): array
{
    $product = Product::factory()->create();

    if ($withMedia) {
        $product->addMedia(UploadedFile::fake()->image('thumb.jpg'))
            ->withCustomProperties(['primary' => true])
            ->toMediaCollection(config('lunar.media.collection'));
    }

    $discount = Discount::factory()->create();
    $discount->discountables()->create([
        'discountable_type' => $product->getMorphClass(),
        'discountable_id' => $product->id,
        'type' => $type,
    ]);

    return [$discount, $product->fresh()];
}

it('renders the discountable product thumbnail', function (string $manager, string $type) {
    [$discount, $product] = discountWithProduct($type, withMedia: true);

    // With no host on the public disk URL, Spatie returns a root-relative URL.
    expect($product->getThumbnailImage())->toStartWith('/storage/');

    Livewire::test($manager, ['ownerRecord' => $discount, 'pageClass' => 'discountableRelationManager'])
        ->assertSuccessful()
        ->assertSeeHtml('src="'.e(url($product->getThumbnailImage())).'"');
})->with([
    'conditions' => [ProductConditionRelationManager::class, 'condition'],
    'rewards' => [ProductRewardRelationManager::class, 'reward'],
]);

it('renders a discountable product without media without an empty image source', function (string $manager, string $type) {
    [$discount] = discountWithProduct($type, withMedia: false);

    Livewire::test($manager, ['ownerRecord' => $discount, 'pageClass' => 'discountableRelationManager'])
        ->assertSuccessful()
        ->assertDontSeeHtml('src=""');
})->with([
    'conditions' => [ProductConditionRelationManager::class, 'condition'],
    'rewards' => [ProductRewardRelationManager::class, 'reward'],
]);
