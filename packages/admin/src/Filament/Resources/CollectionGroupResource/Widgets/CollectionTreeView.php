<?php

namespace Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class CollectionTreeView extends Widget
{
    public ?Model $record = null;

    protected static string $view = 'lunarpanel::resources.collectiongroup-resource.widgets.collection-treeview';
}
