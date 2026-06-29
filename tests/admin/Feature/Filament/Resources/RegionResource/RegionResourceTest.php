<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\RegionResource;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.region');

it('can render the region list page', function () {
    $this->asStaff(admin: true)
        ->get(RegionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render the region create page', function () {
    $this->asStaff(admin: true)
        ->get(RegionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create a region with a display preference and served countries', function () {
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => true]);
    $countries = Country::factory(2)->create();

    $component = Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(RegionResource\Pages\CreateRegion::class)
        ->fillForm([
            'name' => 'United Kingdom',
            'handle' => 'uk',
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
            'language_id' => $language->id,
            'prices_inc_tax' => 'inclusive',
            'countries' => $countries->pluck('id')->all(),
            'default' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Region::class, [
        'handle' => 'uk',
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'language_id' => $language->id,
        'prices_inc_tax' => true,
    ]);

    $region = Region::firstWhere('handle', 'uk');
    expect($region->countries()->count())->toBe(2);
});

it('stores a null display preference when left on the store default', function () {
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => true]);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(RegionResource\Pages\CreateRegion::class)
        ->fillForm([
            'name' => 'Europe',
            'handle' => 'eu',
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
            'language_id' => $language->id,
            'prices_inc_tax' => 'inherit',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Region::firstWhere('handle', 'eu')->prices_inc_tax)->toBeNull();
});
