<?php

namespace Lunar\Admin\Support\Resources\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class ManageUrlsRelatedRecords extends ManageRelatedRecords
{
    protected static string $relationship = 'urls';

    protected static string $model = Model::class;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::relationmanagers.urls.title_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::urls');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::relationmanagers.urls.title_plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('default')->label(
                    __('lunarpanel::relationmanagers.urls.form.default.label')
                )->columnSpan(2),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('slug')
                        ->label(
                            __('lunarpanel::relationmanagers.urls.table.slug.label')
                        )
                        ->required()
                        ->dehydrateStateUsing(
                            fn ($state) => Str::slug($state)
                        )->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: function (Unique $rule, callable $get) {
                                return $rule
                                    ->where('element_type', static::$model)
                                    ->where('language_id', $get('language_id'));
                            }
                        )
                        ->maxLength(255)
                        ->required(),
                    Forms\Components\Select::make('language_id')->label(
                        __('lunarpanel::relationmanagers.urls.table.language.label')
                    )->relationship(name: 'language', titleAttribute: 'name')->required()->reactive(),
                ])->columns(2)->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('language_id')->orderBy('default', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('slug')->label(
                    __('lunarpanel::relationmanagers.urls.table.slug.label')
                ),
                Tables\Columns\TextColumn::make('language.name')->label(
                    __('lunarpanel::relationmanagers.urls.table.language.label')
                ),
                Tables\Columns\IconColumn::make('default')
                    ->label(
                        __('lunarpanel::relationmanagers.urls.table.default.label')
                    )
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('language_id')
                    ->label(
                        __('lunarpanel::relationmanagers.urls.filters.language_id.label')
                    )
                    ->relationship('language', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(
                    __('lunarpanel::relationmanagers.urls.actions.create.label')
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
