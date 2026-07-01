<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\PromotionResource;
use Lunar\Admin\Filament\Resources\PromotionResource\Pages\CreatePromotion;
use Lunar\Admin\Filament\Resources\PromotionResource\Pages\EditPromotion;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Promotion;
use Lunar\Filament\RelationManagers\Promotion\DiscountsRelationManager;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.promotion');

it('can render the promotion list page', function () {
    $this->asStaff(admin: true)
        ->get(PromotionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render the promotion create page', function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->asStaff(admin: true)
        ->get(PromotionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create a promotion', function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreatePromotion::class)
        ->fillForm([
            'name' => ['en' => 'World Cup 2026'],
            'handle' => 'world-cup-2026',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $promotion = Promotion::firstWhere('handle', 'world-cup-2026');

    expect($promotion)->not->toBeNull();
    expect($promotion->translate('name'))->toBe('World Cup 2026');
});

it('renders the discounts relation manager with the campaign discounts', function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $promotion = Promotion::factory()->create();
    $discount = Discount::factory()->create([
        'promotion_id' => $promotion->id,
        'name' => 'World Cup shirts',
    ]);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(DiscountsRelationManager::class, [
            'ownerRecord' => $promotion,
            'pageClass' => EditPromotion::class,
        ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$discount]);
});
