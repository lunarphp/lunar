<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Actions\Products\ForceDeleteProductAction;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProduct extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Basic Information';

    public static bool $formActionsAreSticky = true;

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::basic-information');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\EditAction::make('update_status')
                ->label(
                    __('lunarpanel::product.actions.edit_status.label')
                )
                ->modalHeading(
                    __('lunarpanel::product.actions.edit_status.heading')
                )
                ->record(
                    $this->record
                )->form([
                    Forms\Components\Radio::make('status')->options([
                        'published' => __('lunarpanel::product.form.status.options.published.label'),
                        'draft' => __('lunarpanel::product.form.status.options.draft.label'),
                    ])
                        ->descriptions([
                            'published' => __('lunarpanel::product.form.status.options.published.description'),
                            'draft' => __('lunarpanel::product.form.status.options.draft.description'),
                        ])->live(),
                ]),
            Actions\DeleteAction::make(),
            ForceDeleteProductAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
