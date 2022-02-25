<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Hub\Models\SavedSearch;
use GetCandy\Models\Language;

trait WithSavedSearches
{
    /**
     * Whether we are saving the current search.
     *
     * @var boolean
     */
    public $showSaveSearch = false;

    public SavedSearch $savedSearch;

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

        $this->notify('Search saved');

        $this->savedSearch = new SavedSearch;
        $this->showSaveSearch = false;
    }

    /**
     * Apply the saved search
     *
     * @return void
     */
    public function applySavedSearch($id)
    {
        $savedSearch = $this->savedSearches->first(
            fn($search) => $search->id == $id
        );

        $this->filters = $savedSearch->filters;
        $this->search = $savedSearch->term;
    }

    public function resetSearch()
    {
        $this->search = null;

        foreach ($this->filters as $key => $filter) {
            $this->filters[$key] = null;
        }
    }

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
}
