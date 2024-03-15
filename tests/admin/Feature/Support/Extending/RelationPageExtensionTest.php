<?php

use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Lunar\Admin\Support\Facades\LunarPanel;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending');

it('can customise page headings', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\RelationPageExtension
    {
        public function heading($title, Model $record): string
        {
            return 'New Heading';
        }

        public function subheading($title, Model $record): ?string
        {
            return 'New Subheading';
        }
    };

    \Lunar\Models\Language::factory()->create();
    $product = \Lunar\Models\Product::factory()->create();

    LunarPanel::extensions([
        ManageProductMedia::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(ManageProductMedia::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertSee('New Heading')
        ->assertSee('New Subheading');
});
