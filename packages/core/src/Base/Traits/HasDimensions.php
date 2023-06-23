<?php

namespace Lunar\Base\Traits;

use Cartalyst\Converter\Laravel\Facades\Converter;

trait HasDimensions
{
    /**
     * Method when trait is booted.
     *
     * @return void
     */
    public static function bootHasDimensions()
    {
        self::retrieved(function ($model) {
            $model->mergeCasts([
                'length_value' => 'float',
                'width_value' => 'float',
                'height_value' => 'float',
                'volume_value' => 'float',
                'weight_value' => 'float',
            ]);
        });
    }

    /**
     * Getter for the length attribute.
     *
     * @return \Cartalyst\Converter\Converter
     */
    public function getLengthAttribute()
    {
        $unit = $this->length_unit ?: 'mm';

        return Converter::from("length.{$unit}")->value($this->length_value ?: 0);
    }

    /**
     * Getter for the width attribute.
     *
     * @return \Cartalyst\Converter\Converter
     */
    public function getWidthAttribute()
    {
        $unit = $this->width_unit ?: 'mm';

        return Converter::from("length.{$unit}")->value($this->width_value ?: 0);
    }

    /**
     * Getter for height attribute.
     *
     * @return \Cartalyst\Converter\Converter
     */
    public function getHeightAttribute()
    {
        $unit = $this->height_unit ?: 'mm';

        return Converter::from("length.{$unit}")->value($this->height_value ?: 0);
    }

    /**
     * Getter for weight attribute.
     *
     * @return \Cartalyst\Converter\Converter
     */
    public function getWeightAttribute()
    {
        $unit = $this->weight_unit ?: 'kg';

        return Converter::from("weight.{$unit}")->value($this->weight_value ?: 0);
    }

    /**
     * Getter for the volume attribute.
     *
     * @return \Cartalyst\Converter\Converter
     */
    public function getVolumeAttribute()
    {
        if ($this->volume_value && $this->volume_unit) {
            return Converter::from("volume.{$this->volume_unit}")
                ->to("volume.{$this->volume_unit}")->value($this->volume_value);
        }

        $length = $this->length->to('length.cm')->convert()->getValue();
        $width = $this->width->to('length.cm')->convert()->getValue();
        $height = $this->height->to('length.cm')->convert()->getValue();

        return Converter::from('volume.ml')->to('volume.l')->value($length * $width * $height)->convert();
    }
}
