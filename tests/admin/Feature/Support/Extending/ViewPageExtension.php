<?php

use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Admin\Filament\Widgets;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending.view');

beforeEach(function () {
    $this->asStaff();

    $currency = \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $country = \Lunar\Models\Country::factory()->create();

    $this->order = \Lunar\Models\Order::factory()
        ->for(\Lunar\Models\Customer::factory())
        ->has(\Lunar\Models\OrderAddress::factory()->state([
            'type' => 'shipping',
            'country_id' => $country->id,
        ]), 'shippingAddress')
        ->has(\Lunar\Models\OrderAddress::factory()->state([
            'type' => 'billing',
            'country_id' => $country->id,
        ]), 'billingAddress')
        ->create([
            'currency_code' => $currency->code,
            'meta' => [
                'additional_info' => Str::random(),
            ],
        ]);

});

it('can extend Infolist', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\ViewPageExtension
    {
        public function extendsInfolist(Infolist $infolist): Infolist
        {
            return $infolist->schema([
                ...$infolist->getComponents(true),
                \Filament\Infolists\Components\TextEntry::make('custom_title')
                    ->label('custom_title'),
            ]);
        }
    };

    LunarPanel::registerExtension($class, ManageOrder::class);

    \Livewire\Livewire::test(ManageOrder::class, [
        'record' => $this->order->getRouteKey(),
    ])
    ->assertSee($this->order->reference)
    ->assertSee('custom_title');
});
