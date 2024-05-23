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
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('custom_properties.primary')
                    ->inline(false),
                Forms\Components\FileUpload::make('media')
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

                return $product->getMediaCollectionTitle(config('lunar.media.collection')) ?? Str::ucfirst($this->mediaCollection);
            })
            ->description(function () {
                $product = $this->getOwnerRecord();

                return $product->getMediaCollectionDescription(config('lunar.media.collection')) ?? '';
            })
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', $this->mediaCollection)->orderBy('order_column'))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->state(function (Media $record): string {
                        return $record->hasGeneratedConversion('small') ? $record->getUrl('small') : '';
                    }),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File'),
                Tables\Columns\TextColumn::make('custom_properties.name')
                    ->label('Name'),
                Tables\Columns\IconColumn::make('custom_properties.primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
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
                    ->label('View')
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
