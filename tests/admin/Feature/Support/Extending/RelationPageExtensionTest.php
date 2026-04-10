<?php

use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Lunar\Admin\Support\Extending\RelationPageExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending');

it('can customise page headings', function () {
    $class = new class extends RelationPageExtension
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

    Language::factory()->create();
    $product = Product::factory()->create();

    LunarPanel::extensions([
        ManageProductMedia::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(ManageProductMedia::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertSee('New Heading')
        ->assertSee('New Subheading');
});
