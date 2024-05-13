<?php

namespace Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Support\Actions\Collections\CreateChildCollection;
use Lunar\Admin\Support\Actions\Collections\CreateRootCollection;
use Lunar\Admin\Support\Actions\Collections\DeleteCollection;
use Lunar\Admin\Support\Actions\Collections\MoveCollection;
use Lunar\Facades\DB;
use Lunar\Models\Collection;

class CollectionTreeView extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?Model $record = null;

    public ?int $parentId = null;

    public iterable $nodes = [];

    protected static bool $isLazy = false;

    protected static string $view = 'lunarpanel::resources.collectiongroup-resource.widgets.collection-treeview';

    public function mount()
    {
        $this->loadRootNodes();
    }

    protected function loadRootNodes()
    {
        $this->nodes = static::mapCollections(
            $this->record->collections()
                ->withCount('children')
                ->whereIsRoot()
                ->defaultOrder()
                ->get()
        );
    }

    public static function mapCollections($collections)
    {

        return collect($collections)->map(
            function ($collection) {
                return [
                    'id' => $collection->id,
                    'parent_id' => $collection->parent_id,
                    'name' => $collection->attr('name'),
                    'edit_url' => CollectionResource::getUrl('edit', [
                        'record' => $collection,
                    ]),
                    'thumbnail' => $collection->thumbnail?->getUrl('small'),
                    'children' => [],
                    'children_count' => $collection->children_count,
                ];
            }
        )->toArray();
    }

    public function sort($movedId, $siblingId, $direction = 'after')
    {
        // Look to use `afterNode`
        DB::transaction(function () use ($movedId, $siblingId, $direction) {
            $moved = Collection::find($movedId);
            $sibling = Collection::find($siblingId);
            $parent = $moved->parent;

            if ($direction == 'after') {
                $moved->afterNode($sibling)->save();
            } else {
                $moved->beforeNode($sibling)->save();
            }

            if ($parent) {
                $this->toggleChildren($parent->id, true);
            } else {
                $this->loadRootNodes();
            }
        });

        Notification::make()
            ->title(
                __('lunarpanel::components.collection-tree-view.notifications.collections-reordered.success')
            )
            ->success()
            ->send();
    }

    public function toggleChildren($nodeId, $keepOpen = false)
    {
        $index = $this->findIndex($nodeId, $this->nodes);

        if (! $index) {
            Notification::make()
                ->title(
                    __('lunarpanel::components.collection-tree-view.notifications.node-expanded.danger')
                )
                ->danger()
                ->send();

            return;
        }

        $index = implode('.children.', $index);

        $nodes = Arr::get($this->nodes, $index.'.children', []);

        $childNodes = [];

        if (! count($nodes) || $keepOpen) {
            $childNodes = static::mapCollections(
                Collection::whereParentId($nodeId)->withCount('children')->defaultOrder()->get()
            );
        }

        Arr::set($this->nodes, $index.'.children', $childNodes);

        if (count($childNodes)) {
            Arr::set($this->nodes, $index.'.children_count', count($childNodes));
        }
    }

    public function deleteAction()
    {
        return DeleteCollection::make('delete')
            ->after(function (array $arguments) {
                $index = $this->findIndex($arguments['id'], $this->nodes);

                $index = implode('.children.', $index);

                Arr::pull($this->nodes, $index);
            })->icon(
                fn () => FilamentIcon::resolve('actions::delete-action')
            );
    }

    public function addChildCollectionAction()
    {
        return CreateChildCollection::make('addChildCollection')
            ->icon(
                fn () => FilamentIcon::resolve('lunar::sub-collection')
            )->after(
                fn (array $arguments) => $this->toggleChildren($arguments['id'], keepOpen: true)
            );
    }

    public function makeRootAction()
    {
        return Action::make('makeRoot')->requiresConfirmation()->icon(
            fn () => FilamentIcon::resolve('actions::make-collection-root-action')
        )->action(function (array $arguments) {
            $collection = Collection::find($arguments['id']);
            $collection->makeRoot()->save();
            $this->loadRootNodes();
        })->hidden(function (array $arguments) {
            return Collection::find($arguments['id'])->isRoot();
        });
    }

    public function createRootCollectionAction()
    {
        return CreateRootCollection::make('createRootCollection')
            ->mutateFormDataUsing(function (array $data) {
                $data['collection_group_id'] = $this->record->id;

                return $data;
            })->after(
                fn () => $this->loadRootNodes()
            );
    }

    public function moveAction()
    {
        return MoveCollection::make('move')
            ->icon(
                fn () => FilamentIcon::resolve('lunar::move-collection')
            )->form([
                Forms\Components\Select::make('target_id')
                    ->label(
                        __('lunarpanel::components.collection-tree-view.actions.move.form.target_id.label')
                    )
                    ->model(Collection::class)
                    ->searchable()
                    ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                        return Collection::search($search)
                            ->get()
                            ->mapWithKeys(fn (Collection $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                            ->all();
                    }),
            ])->after(
                fn (array $data) => $this->loadRootNodes()
            );
    }

    public function getTreeActions()
    {
        return [
            $this->makeRootAction,
            $this->moveAction,
            $this->addChildCollectionAction,
            $this->deleteAction,
        ];
    }

    public function getTreeNodesProperty()
    {
        return Collection::query()
            ->when($this->record?->id,
                fn ($query, $groupId) => $query->whereCollectionGroupId($groupId)
            )->when($this->parentId,
                fn ($query, $parentId) => $query->whereParentId($parentId)
            )->withCount('children')->whereIsRoot()->defaultOrder()->get();
    }

    protected function findIndex($nodeId, $nodes)
    {
        foreach ($nodes as $nodeIndex => $node) {
            if ($nodeId == $node['id']) {
                return [$nodeIndex];
            } else {
                $callback = $this->findIndex($nodeId, $node['children']);
                if ($callback) {
                    return array_merge([$nodeIndex], $callback);
                }
            }
        }

        return false;
    }
}
