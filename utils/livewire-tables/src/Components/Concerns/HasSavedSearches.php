<?php

namespace Lunar\LivewireTables\Components\Concerns;

use Illuminate\Support\Collection;

trait HasSavedSearches
{
    /**
     * Whether the table can save searches.
     */
    public bool $canSaveSearches = false;

    /**
     * The saved search reference
     */
    public ?string $savedSearch = null;

    /**
     * The new name for the saved search.
     *
     * @var string
     */
    public ?string $savedSearchName = null;

    /**
     * Return the saved searches available to the table.
     */
    public function getSavedSearchesProperty(): Collection
    {
        return collect();
    }

    public function mountHasSavedSearches()
    {
        $this->applySavedSearchToQuery($this->savedSearch);
    }

    /**
     * Apply the saved search to the table.
     *
     * @param  string  $key
     * @return void
     */
    public function applySavedSearch($key)
    {
        $this->resetPage();

        $this->filters = [];
        $this->query = null;

        if ($this->savedSearch == $key) {
            $this->savedSearch = null;

            return;
        }

        $this->applySavedSearchToQuery($key);

        $this->savedSearch = $key;
    }

    protected function applySavedSearchToQuery($key)
    {
        if ($key) {
            $search = $this->savedSearches->first(function ($search) use ($key) {
                return $search['key'] == $key;
            });

            if ($search) {
                $this->filters = $search['filters'];
                $this->query = $search['query'];
            }
        }
    }

    /**
     * Save the current search filters and query
     *
     * @return void
     */
    public function saveSearch()
    {
        //
    }

    /**
     * Reset the saved search state to default.
     *
     * @return void
     */
    public function resetSavedSearch()
    {
        $this->resetPage();

        $this->savedSearch = false;
    }

    /**
     * Return whether the search state has filters or search applied.
     */
    public function getHasSearchAppliedProperty(): bool
    {
        if ($this->query) {
            return true;
        }

        $applied = false;

        foreach ($this->filters as $filter) {
            if ($filter) {
                $applied = true;
            }
        }

        return $applied;
    }
}
