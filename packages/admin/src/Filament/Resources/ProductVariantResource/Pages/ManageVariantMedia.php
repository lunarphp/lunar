<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Awcodes\Shout\Components\Shout;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ManageVariantMedia extends BaseManageRelatedRecords
{
    protected static string $relationship = 'images';

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

    public function table(Table $table): Table
    {
        return $table
            ->heading(function () {
                return __('lunarpanel::relationmanagers.medias.title');
            })
            ->description(function () {
                return __('lunarpanel::relationmanagers.medias.variant_description');
            })
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('position'))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->state(function (Media $record): string {
                        return $record->hasGeneratedConversion('small') ? $record->getUrl('small') : $record->getUrl();
                    })
                    ->label(__('lunarpanel::relationmanagers.medias.table.image.label')),
                Tables\Columns\TextColumn::make('file_name')
                    ->limit(30)
                    ->label(__('lunarpanel::relationmanagers.medias.table.file.label')),
                Tables\Columns\TextColumn::make('custom_properties.name')
                    ->label(__('lunarpanel::relationmanagers.medias.table.name.label')),
                Tables\Columns\ToggleColumn::make('primary')
                    ->label(__('lunarpanel::relationmanagers.medias.table.primary.label'))
                    ->beforeStateUpdated(function ($record, $state) {
                        if ($state === true) {
                            $record = $this->getOwnerRecord();

                            $record->images->each(fn ($media) => $record->images()->updateExistingPivot($media->id, ['primary' => false]));
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make('attach')
                    ->label(__('lunarpanel::relationmanagers.medias.actions.attach.label'))
                    ->modalHeading(__('lunarpanel::relationmanagers.medias.actions.attach.label'))
                    ->modalWidth(\Filament\Support\Enums\MaxWidth::Medium)
                    ->form([
                        Shout::make('no_media_available')->content(
                            __('lunarpanel::relationmanagers.medias.all_media_attached')
                        )->visible(
                            fn (Get $get) => $this->getRecord()->product->media()->count() <= $this->getRecord()->images()->count()
                        ),
                        Forms\Components\Select::make('media_id')
                            ->label(__('lunarpanel::relationmanagers.medias.table.file.label'))
                            ->options(function () {
                                return $this->getRecord()
                                    ->product
                                    ->media
                                    ->filter(fn ($media) => ! $this->getRecord()->images->pluck('id')->contains($media->id))
                                    ->mapWithKeys(function ($media) {
                                        $imageUrl = $media->hasGeneratedConversion('small') ? $media->getUrl('small') : $media->getUrl();
                                        $imageTag = '<img src="'.$imageUrl.'" alt="" style="width: 75px; display: inline; margin-right: 10px">';
                                        $customName = Arr::get($media->custom_properties, 'name');
                                        $name = empty($customName) ? $media->name : $customName;

                                        return [$media->getKey() => $imageTag.$name];
                                    });
                            })
                            ->disabled(
                                fn () => $this->getRecord()->product->media()->count() <= $this->getRecord()->images()->count()
                            )
                            ->native(false)
                            ->allowHtml()
                            ->required(),

                        Forms\Components\Toggle::make('primary')
                            ->label(__('lunarpanel::relationmanagers.medias.table.primary.label'))
                            ->visible(
                                fn () => $this->getRecord()->product->media()->count() > $this->getRecord()->images()->count()
                            ),
                    ])
                    ->using(function (array $data): Model {
                        $record = $this->getOwnerRecord();

                        $isPrimary = $data['primary'] ?? false;
                        $position = 0;

                        if (! $isPrimary && $record->images->isEmpty()) {
                            $isPrimary = true;
                        }

                        if ($record->images->isNotEmpty()) {
                            $position = $record->images->pluck('pivot.position')->sort()->last() + 1;

                            if ($isPrimary) {
                                $record->images->each(fn ($media) => $record->images()->updateExistingPivot($media->id, ['primary' => false]));
                            }
                        }

                        $record->images()->attach([
                            $data['media_id'] => [
                                'position' => $position,
                                'primary' => $isPrimary,
                            ],
                        ]);

                        return $record;
                    }),
            ])
            ->actions([
                Action::make('detach')
                    ->label(__('lunarpanel::relationmanagers.medias.actions.detach.label'))
                    ->action(function ($record) {
                        $this->getOwnerRecord()->images()->detach($record);
                    }),
            ])
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering) => $action
                    ->button(),
            )
            ->reorderable('position', true);
    }
}
