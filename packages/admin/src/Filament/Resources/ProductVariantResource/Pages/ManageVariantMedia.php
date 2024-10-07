<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Awcodes\Shout\Components\Shout;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Forms\Components\MediaSelect;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Models\Contracts\ProductVariant as ProductVariantContract;

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
        return [
            ProductVariantResource::getVariantSwitcherWidget(
                $this->getRecord()
            ),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url(function (Model $record) {
            return ProductResource::getUrl('variants', [
                'record' => $record->product,
            ]);
        });
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
                Shout::make('no_selection')->content(
                    __('lunarpanel::productvariant.pages.media.form.no_selection.label')
                )->visible(
                    fn (Get $get) => ! $get('images') && $this->getRecord()->product->media()->count()
                ),
                Shout::make('no_media_available')->content(
                    __('lunarpanel::productvariant.pages.media.form.no_media_available.label')
                )->visible(
                    fn (Get $get) => ! $this->getRecord()->product->media()->count()
                ),
                MediaSelect::make('images')
                    ->visible(
                        fn () => $this->getRecord()->product->media()->count()
                    )
                    ->label(
                        __('lunarpanel::productvariant.pages.media.form.images.label')
                    )
                    ->helperText(
                        __('lunarpanel::productvariant.pages.media.form.images.helper_text')
                    )
                    ->afterStateHydrated(function (ProductVariantContract $record, MediaSelect $component) {
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
