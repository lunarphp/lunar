<?php

namespace Lunar\Base\Traits;

use Cartalyst\Converter\Laravel\Facades\Converter;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasDimensions
{
    /**
     * Initialize the trait
     *
     * @return void
     */
    public function initializeHasDimensions()
    {
        $this->mergeCasts([
            'length_value' => 'float',
            'width_value' => 'float',
            'height_value' => 'float',
            'volume_value' => 'float',
            'weight_value' => 'float',
        ]);
    }

    /**
     * Get the length unit
     */
    protected function lengthUnit(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value
                ?? collect(config('lunar.shipping.measurements.length'))->where('default')->keys()->first()
                ?? 'mm'
        );
    }

    /**
     * Get the width unit
     */
    protected function widthUnit(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value
                ?? collect(config('lunar.shipping.measurements.length'))->where('default')->keys()->first()
                ?? 'mm'
        );
    }

    /**
     * Get the height unit
     */
    protected function heightUnit(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value
                ?? collect(config('lunar.shipping.measurements.length'))->where('default')->keys()->first()
                ?? 'mm'
        );
    }

    /**
     * Get the weight unit
     */
    protected function weightUnit(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value
                ?? collect(config('lunar.shipping.measurements.weight'))->where('default')->keys()->first()
                ?? 'kg'
        );
    }

    /**
     * Get the volume unit
     */
    protected function volumeUnit(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value
                ?? collect(config('lunar.shipping.measurements.volume'))->where('default')->keys()->first()
                ?? 'l'
        );
    }

    /**
     * Get the length
     */
    protected function length(): Attribute
    {
        return Attribute::make(
            get: fn() => Converter::from("length.{$this->length_unit}")->value($this->length_value ?? 0)
        );
    }

    /**
     * Get the width
     */
    protected function width(): Attribute
    {
        return Attribute::make(
            get: fn() => Converter::from("length.{$this->width_unit}")->value($this->width_value ?? 0)
        );
    }

    /**
     * Get the height
     */
    protected function height(): Attribute
    {
        return Attribute::make(
            get: fn() => Converter::from("length.{$this->height_unit}")->value($this->height_value ?? 0)
        );
    }

    /**
     * Get the weight
     */
    protected function weight(): Attribute
    {
        return Attribute::make(
            get: fn() => Converter::from("weight.{$this->weight_unit}")->value($this->weight_value ?? 0)
        );
    }

    /**
     * Get the volume
     */
    protected function volume(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->volume_value && $this->volume_unit) {
                    return Converter::from("volume.{$this->volume_unit}")
                        ->to("volume.{$this->volume_unit}")->value($this->volume_value);
                }

                $length = $this->length->to('length.cm')->convert()->getValue();
                $width = $this->width->to('length.cm')->convert()->getValue();
                $height = $this->height->to('length.cm')->convert()->getValue();

                return Converter::from('volume.ml')->to('volume.l')->value($length * $width * $height)->convert();
            }
        );
    }
}
