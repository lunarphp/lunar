<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Languages;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Language;

class LanguageShow extends Component
{
    use Notifies;

    /**
     * The current language we're showing.
     *
     * @var \Lunar\Models\Language
     */
    public Language $language;

    /**
     * Defines the confirmation text when deleting a language.
     *
     * @var string|null
     */
    public $deleteConfirm = null;

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'language.name' => 'required|string|max:255',
            'language.code' => 'required|string|max:255',
            'language.default' => 'nullable|boolean',
        ];
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->language->save();

        $this->notify(
            'Language successfully updated.',
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
     * Soft deletes a language.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            return;
        }

        $this->language->delete();

        $this->notify(
            'Language successfully deleted.',
            'hub.languages.index'
        );
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->language->name;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.languages.show')
            ->layout('adminhub::layouts.base');
    }
}
