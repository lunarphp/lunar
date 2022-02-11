<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tags;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Tag;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TagShow extends Component
{
    public Tag $tag;
    use Notifies;

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'tag.value' => 'required|max:255|unique:'.$this->tag->getTable().',value,'.$this->tag->id,
        ];
    }

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
     * Update the tag.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        $this->tag->save();
        $this->notify(__('adminhub::settings.tags.form.notify.updated'), 'hub.tags.index');
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
