<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Actions\Products\DeleteProduct;
use Lunar\Core\Models\Product;

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
                ->label(fn (Product $record) => __('lunarpanel::product.actions.edit_status.label_with_state', [
                    'state' => $record->status->label(),
                ]))
                ->color(fn (Product $record) => match ((string) $record->status) {
                    'published' => 'success',
                    'archived' => 'gray',
                    default => 'warning',
                })
                ->modalHeading(__('lunarpanel::product.actions.edit_status.heading'))
                ->record($this->record)
                ->schema([
                    Radio::make('status')
                        ->options(fn (Product $record) => static::statusOptionsFor($record))
                        ->descriptions(fn (Product $record) => static::statusDescriptionsFor($record))
                        ->live(),
                ]),
            DeleteAction::make()
                ->modalDescription(fn (Product $record): string => $record->hasOrderHistory()
                    ? __('lunarpanel::product.actions.delete.blocked')
                    : __('lunarpanel::product.actions.delete.confirm'))
                ->disabled(fn (Product $record): bool => $record->hasOrderHistory())
                ->tooltip(fn (Product $record): ?string => $record->hasOrderHistory()
                    ? __('lunarpanel::product.actions.delete.disabled_tooltip')
                    : null)
                ->before(function (Product $record, DeleteAction $action) {
                    if (DeleteProduct::isProtected($record)) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunarpanel::product.actions.delete.blocked'))
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    /**
     * Status options keyed by ProductState $name. Restricted to the current
     * state plus its allowed transitions, so the form can't pick an illegal
     * transition (e.g. Draft → Archived directly).
     *
     * @return array<string, string>
     */
    protected static function statusOptionsFor(Product $record): array
    {
        return collect($record->status->transitionableStates())
            ->prepend((string) $record->status)
            ->unique()
            ->mapWithKeys(fn (string $name) => [
                $name => __("lunarpanel::product.form.status.options.{$name}.label"),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected static function statusDescriptionsFor(Product $record): array
    {
        return collect(static::statusOptionsFor($record))
            ->keys()
            ->mapWithKeys(fn (string $name) => [
                $name => __("lunarpanel::product.form.status.options.{$name}.description"),
            ])
            ->all();
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
