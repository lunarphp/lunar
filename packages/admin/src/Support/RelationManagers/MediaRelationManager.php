<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Admin\Events\ModelMediaUpdated;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'media';

    public string $mediaCollection = 'default';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('custom_properties.name')
                    ->label(__('lunarpanel::relationmanagers.medias.form.name.label'))
                    ->maxLength(255),
                Forms\Components\Toggle::make('custom_properties.primary')
                    ->label(__('lunarpanel::relationmanagers.medias.form.primary.label'))
                    ->inline(false),
                Forms\Components\FileUpload::make('media')
                    ->label(__('lunarpanel::relationmanagers.medias.form.media.label'))
                    ->columnSpan(2)
                    ->hiddenOn('edit')
                    ->storeFiles(false)
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(function () {
                $product = $this->getOwnerRecord();

                return $product->getMediaCollectionTitle($this->mediaCollection) ?? Str::ucfirst($this->mediaCollection);
            })
            ->description(function () {
                $product = $this->getOwnerRecord();

                return $product->getMediaCollectionDescription($this->mediaCollection) ?? '';
            })
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', $this->mediaCollection)->orderBy('order_column'))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->state(function (Media $record): string {
                        return $record->hasGeneratedConversion('small') ? $record->getUrl('small') : '';
                    })
                    ->label(__('lunarpanel::relationmanagers.medias.table.image.label')),
                Tables\Columns\TextColumn::make('file_name')
                    ->limit(30)
                    ->label(__('lunarpanel::relationmanagers.medias.table.file.label')),
                Tables\Columns\TextColumn::make('custom_properties.name')
                    ->label(__('lunarpanel::relationmanagers.medias.table.name.label')),
                Tables\Columns\IconColumn::make('custom_properties.primary')
                    ->label(__('lunarpanel::relationmanagers.medias.table.primary.label'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('lunarpanel::relationmanagers.medias.actions.create.label'))
                    ->using(function (array $data, string $model): Model {
                        $product = $this->getOwnerRecord();

                        return $product->addMedia($data['media'])
                            ->withCustomProperties([
                                'name' => $data['custom_properties']['name'],
                                'primary' => $data['custom_properties']['primary'],
                            ])
                            ->preservingOriginal()
                            ->toMediaCollection($this->mediaCollection);
                    })->after(
                        fn () => ModelMediaUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->after(
                    fn () => ModelMediaUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
                Tables\Actions\DeleteAction::make(),
                Action::make('view_open')
                    ->label(__('lunarpanel::relationmanagers.medias.actions.view.label'))
                    ->icon('lucide-eye')
                    ->url(fn (Media $record): string => $record->getUrl())
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->after(
                        fn () => ModelMediaUpdated::dispatch(
                            $this->getOwnerRecord()
                        )
                    ),
                ]),
            ])
            ->reorderable('order_column');
    }
}
