<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Models\Country;

trait WithCountries
{
    /**
     * Getter for all languages in the system.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCountriesProperty()
    {
        return Country::get();
    }
}
