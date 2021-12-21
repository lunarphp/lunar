<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tags;

use GetCandy\Models\Tag;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TagShow extends Component
{
    public Tag $tag;

    public function getTaggablesProperty()
    {
        $prefix = config('getcandy.database.table_prefix');

        return DB::table(
            "{$prefix}taggables"
        )->select([
            'taggable_type',
            DB::RAW('COUNT(*) as count'),
        ])->groupBy('taggable_type')->get();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.tags.show')
            ->layout('adminhub::layouts.base');
    }
}
