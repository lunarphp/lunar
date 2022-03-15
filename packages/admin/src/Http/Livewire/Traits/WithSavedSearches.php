<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Models\SavedSearch;

trait WithSavedSearches
{
    /**
     * Whether we are saving the current search.
     *
     * @var bool
     */
    public $showSaveSearch = false;

    /**
     * The ID of the saved search to delete.
     *
     * @var string|int
     */
    public $savedSearchToDelete = 5;

    /**
     * The saved search instance to create.
     *
     * @var SavedSearch
     */
    public SavedSearch $savedSearch;

    /**
     * Mount the livewire trait.
     *
     * @return void
     */
    public function mountWithSavedSearches()
    {
        $this->savedSearch = new SavedSearch;
    }

    /**
     * Return the validation rules for the trait.
     *
     * @return array
     */
    public function withSavedSearchesValidationRules()
    {
        return [
            'savedSearch.name' => 'required',
        ];
    }

    /**
     * Getter for default language.
     *
     * @return \GetCandy\Models\Language
     */
    public function getSavedSearchesProperty()
    {
        return auth()->user()->savedSearches()->whereComponent(
            $this->getName()
        )->get();
    }

    /**
     * Return the active saved search.
     *
     * @return null|\GetCandy\Hub\Models\SavedSearch
     */
    public function getActiveSavedSearchProperty()
    {
        return $this->savedSearches->first(function ($search) {
            return $search->term == $this->search && $search->filters == $this->filters;
        });
    }

    /**
     * Save the search.
     *
     * @return void
     */
    public function saveSearch()
    {
        $this->savedSearch->term = $this->search;
        $this->savedSearch->filters = $this->filters;
        $this->savedSearch->component = $this->getName();

        auth()->user()->savedSearches()->save($this->savedSearch);

        $this->notify(
            __('adminhub::notifications.saved_searches.saved')
        );

        $this->savedSearch = new SavedSearch;
        $this->showSaveSearch = false;
    }

    /**
     * Apply the saved search.
     *
     * @return void
     */
    public function applySavedSearch($id)
    {
        $savedSearch = $this->savedSearches->first(
            fn ($search) => $search->id == $id
        );

        $this->filters = $savedSearch->filters;
        $this->search = $savedSearch->term;
    }

    /**
     * Reset the search and filters.
     *
     * @return void
     */
    public function resetSearch()
    {
        $this->search = null;

        foreach ($this->filters as $key => $filter) {
            $this->filters[$key] = null;
        }
    }

    /**
     * Returns whether we have custom filters applied.
     *
     * @return bool
     */
    public function getHasCustomFiltersProperty()
    {
        if ($this->search) {
            return true;
        }

        foreach ($this->filters as $filter) {
            if ($filter) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete a saved search.
     *
     * @param  string|int  $id
     * @return void
     */
    public function deleteSavedSearch()
    {
        SavedSearch::whereId($this->savedSearchToDelete)->delete();

        $this->savedSearchToDelete = null;

        $this->notify(
            __('adminhub::notifications.saved_searches.deleted')
        );
    }
}
