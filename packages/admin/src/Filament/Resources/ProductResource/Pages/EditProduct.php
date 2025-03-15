<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Actions;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Actions\Products\ForceDeleteProductAction;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = $this->callLunarHook('beforeUpdate', $data, $record);

        $variantData = Arr::pull($data, 'variant_attributes', null);

        if ($variantData) {
            $variant = $record->variants()->first();
            $variant->attribute_data = collect($variantData);
            $variant->save();
        }

        $record = parent::handleRecordUpdate($record, $data);

        return $this->callLunarHook('afterUpdate', $record, $data);
    }
}
