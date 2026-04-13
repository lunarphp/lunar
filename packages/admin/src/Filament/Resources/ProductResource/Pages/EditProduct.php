<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Radio;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProduct extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    public static bool $formActionsAreSticky = true;

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.edit.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.edit.title');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::basic-information');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            EditAction::make('update_status')
                ->label(
                    __('lunarpanel::product.actions.edit_status.label')
                )
                ->modalHeading(
                    __('lunarpanel::product.actions.edit_status.heading')
                )
                ->record(
                    $this->record
                )->schema([
                    Radio::make('status')->options([
                        'published' => __('lunarpanel::product.form.status.options.published.label'),
                        'draft' => __('lunarpanel::product.form.status.options.draft.label'),
                    ])
                        ->descriptions([
                            'published' => __('lunarpanel::product.form.status.options.published.description'),
                            'draft' => __('lunarpanel::product.form.status.options.draft.description'),
                        ])->live(),
                ]),
            DeleteAction::make(),
            ForceDeleteAction::make()
                ->databaseTransaction(),
            RestoreAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
