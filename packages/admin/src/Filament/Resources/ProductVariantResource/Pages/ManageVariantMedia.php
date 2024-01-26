<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Forms\Components\MediaSelect;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Models\ProductVariant;

class ManageVariantMedia extends BaseEditRecord
{
    protected static string $resource = ProductVariantResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::productvariant.pages.media.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::productvariant.pages.media.title');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::media');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...ProductVariantResource::getBaseBreadcrumbs(
                $this->getRecord()
            ),
            ProductVariantResource::getUrl('media', [
                'record' => $this->getRecord(),
            ]) => $this->getTitle(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->images()->sync([
            $data['images'] => ['primary' => true],
        ]);

        return $record;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                MediaSelect::make('images')
                    ->label(
                        __('lunarpanel::productvariant.pages.media.form.images.label')
                    )
                    ->afterStateHydrated(function (ProductVariant $record, MediaSelect $component) {
                        $image = $record->images->first(function ($media) {
                            return (bool) $media->pivot?->primary;
                        });
                        $component->state($image?->id);
                    })
                    ->options(
                        $this->getRecord()->product->media->mapWithKeys(
                            fn ($media) => [
                                $media->id => $media->getUrl('small'),
                            ]
                        )
                    ),
            ]),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
