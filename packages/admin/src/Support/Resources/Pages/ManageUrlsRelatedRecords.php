<?php

namespace Lunar\Admin\Support\Resources\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;

class ManageUrlsRelatedRecords extends BaseManageRelatedRecords
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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('default')->label(
                    __('lunarpanel::relationmanagers.urls.form.default.label')
                )->columnSpan(2),
                Group::make([
                    TextInput::make('slug')
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
                                    ->where('element_type', (static::$model)::morphName())
                                    ->where('language_id', $get('language_id'));
                            }
                        )
                        ->maxLength(255)
                        ->required(),
                    Select::make('language_id')->label(
                        __('lunarpanel::relationmanagers.urls.table.language.label')
                    )->relationship(name: 'language', titleAttribute: 'name')->required()->live(),
                ])->columns(2)->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('language_id')->orderBy('default', 'desc'))
            ->columns([
                TextColumn::make('slug')->label(
                    __('lunarpanel::relationmanagers.urls.table.slug.label')
                ),
                TextColumn::make('language.name')->label(
                    __('lunarpanel::relationmanagers.urls.table.language.label')
                ),
                IconColumn::make('default')
                    ->label(
                        __('lunarpanel::relationmanagers.urls.table.default.label')
                    )
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('language_id')
                    ->label(
                        __('lunarpanel::relationmanagers.urls.filters.language_id.label')
                    )
                    ->relationship('language', 'name'),
            ])
            ->headerActions([
                CreateAction::make()->label(
                    __('lunarpanel::relationmanagers.urls.actions.create.label')
                ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
