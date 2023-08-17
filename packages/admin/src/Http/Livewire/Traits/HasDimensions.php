<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Cartalyst\Converter\Laravel\Facades\Converter;

trait HasDimensions
{
    /**
     * Whether or not we have manual volume set.
     *
     * @var bool
     */
    public $manualVolume = false;

    /**
     * Mount the trait.
     *
     * @return void
     */
    public function mountHasDimensions()
    {
        $this->manualVolume = (bool) $this->getHasDimensionsModel()->volume_value;
    }

    /**
     * Return the model which has dimensions.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function getHasDimensionsModel();

    /**
     * Return all available dimensions.
     *
     * @return array
     */
    public function getAvailableMeasurementsProperty()
    {
        return Converter::getMeasurements();
    }

    /**
     * Computed getter to return length measurements.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLengthMeasurementsProperty()
    {
        return $this->formatMeasurements(
            $this->availableMeasurements['length'] ?? []
        );
    }

    /**
     * Computed getter to return weight measurements.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWeightMeasurementsProperty()
    {
        return $this->formatMeasurements(
            $this->availableMeasurements['weight'] ?? []
        );
    }

    /**
     * Computed getter to return volume measurements.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVolumeMeasurementsProperty()
    {
        return $this->formatMeasurements(
            $this->availableMeasurements['volume'] ?? []
        );
    }

    /**
     * Format measurements ready for select inputs.
     *
     * @param  array  $measurements
     * @return \Illuminate\Support\Collection
     */
    protected function formatMeasurements($measurements)
    {
        return collect($measurements)
            ->keys();
    }
}
