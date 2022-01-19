<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Attributes;

use Livewire\Component;

class AttributeShow extends Component
{
    /**
     * The type.
     */
    public $type;

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->type = $this->type;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.attributes.show')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.attributes.show.title', [
                    'type' => $this->type,
                ]),
            ]);
    }
}
