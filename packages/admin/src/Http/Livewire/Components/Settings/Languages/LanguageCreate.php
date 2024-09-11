<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Languages;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Language;

class LanguageCreate extends Component
{
    use Notifies;

    /**
     * A new instance of the language model.
     */
    public Language $language;

    /**
     * Called when we mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->language = new Language();
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'language.name' => 'required|string|max:255',
            'language.code' => 'required|string|max:255|unique:'.Language::class.',code',
            'language.default' => 'nullable|boolean',
        ];
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function create()
    {
        $this->validate();

        $this->language->default = (bool) $this->language->default;

        $this->language->save();

        $this->notify(
            __('adminhub::notifications.languages.added'),
            'hub.languages.index'
        );
    }

    /**
     * Toggles the default attribute of the model.
     *
     * @return void
     */
    public function toggleDefault()
    {
        $this->language->default = ! $this->language->default;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.languages.create')
            ->layout('adminhub::layouts.base');
    }
}
