<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Actions\DeleteAction;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Admin\Filament\Resources\CollectionGroupResource;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Filament\Forms\Components\CollectionSelect;

class EditCollection extends BaseEditRecord
{
    protected static string $resource = CollectionResource::class;

    public static bool $formActionsAreSticky = true;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::collection.pages.edit.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::collection.pages.edit.label');
    }

    public function getBreadcrumbs(): array
    {
        return static::getResource()::getCollectionBreadcrumbs(
            $this->getRecord()
        );
    }

    protected function getDefaultHeaderActions(): array
    {
        $record = $this->getRecord();

        $successUrl = CollectionGroupResource::getUrl('edit', [
            'record' => $record->group,
        ]);

        if ($record->parent) {
            $successUrl = CollectionResource::getUrl('edit', [
                'record' => $record->parent,
            ]);
        }

        return [
            DeleteAction::make('delete')->schema([
                CollectionSelect::make('target_collection')
                    ->excludeSelf($record)
                    ->excludeDescendantsOf($record)
                    ->helperText(
                        'Choose which collection the children of this collection should be transferred to.'
                    )->hidden(
                        fn () => ! $record->children()->count()
                    ),
            ])->before(function (Collection $collection, array $data) {

                $targetId = $data['target_collection'] ?? null;

                if ($targetId) {
                    $parent = Collection::find($targetId);

                    DB::beginTransaction();
                    foreach ($collection->children as $child) {
                        $child->prependToNode($parent)->save();
                    }
                    DB::commit();

                } else {
                    $collection->descendants()->delete();
                }
            })->successRedirectUrl($successUrl),
        ];
    }
}
