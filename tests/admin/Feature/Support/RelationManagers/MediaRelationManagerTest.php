<?php

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandMedia;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Lunar\Admin\Support\Extending\RelationManagerExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('support.relation-managers');

it('can render relation manager', function ($model, $page) {
    $this->asStaff();

    Language::factory()->create([
        'default' => true,
    ]);

    $model = $model::factory()->create();

    Livewire::test(MediaRelationManager::class, [
        'ownerRecord' => $model,
        'pageClass' => $page,
    ])->assertSuccessful();
})->with([
    [Product::class, ManageProductMedia::class],
    [Brand::class, ManageBrandMedia::class],
]);

it('persists custom properties added by an extension when creating media', function () {
    $class = new class extends RelationManagerExtension
    {
        public function extendForm(Schema $schema): Schema
        {
            $schema->components([
                ...$schema->getComponents(true),
                TextInput::make('custom_properties.credits'),
            ]);

            return $schema;
        }
    };

    LunarPanel::extensions([
        MediaRelationManager::class => $class::class,
    ]);

    $this->asStaff();

    Language::factory()->create([
        'default' => true,
    ]);

    $brand = Brand::factory()->create();

    Livewire::test(MediaRelationManager::class, [
        'ownerRecord' => $brand,
        'pageClass' => ManageBrandMedia::class,
    ])->callTableAction(CreateAction::class, data: [
        'custom_properties.name' => 'Test image',
        'custom_properties.credits' => 'Jane Doe',
        'media' => UploadedFile::fake()->image('foobar.jpg'),
    ])->assertHasNoTableActionErrors();

    $media = $brand->fresh()->getFirstMedia('default');

    expect($media)->not->toBeNull()
        ->and($media->getCustomProperty('name'))->toBe('Test image')
        ->and($media->getCustomProperty('credits'))->toBe('Jane Doe');
});

it('preserves existing custom properties not present on the edit form', function () {
    $this->asStaff();

    Language::factory()->create([
        'default' => true,
    ]);

    $brand = Brand::factory()->create();

    $media = $brand
        ->addMedia(UploadedFile::fake()->image('foobar.jpg'))
        ->preservingOriginal()
        ->withCustomProperties([
            'name' => 'Original name',
            'sha1' => 'abc123',
        ])
        ->toMediaCollection('default');

    Livewire::test(MediaRelationManager::class, [
        'ownerRecord' => $brand,
        'pageClass' => ManageBrandMedia::class,
    ])->mountTableAction(EditAction::class, $media)
        ->setTableActionData([
            'custom_properties' => [
                'name' => 'Updated name',
                'primary' => false,
            ],
        ])->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $media->refresh();

    expect($media->getCustomProperty('name'))->toBe('Updated name')
        ->and($media->getCustomProperty('sha1'))->toBe('abc123');
});
