<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Database\Factories\ProductOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class ProductOption extends BaseModel
{
    use HasFactory, HasMedia, HasTranslations, Searchable;

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'product_options_'.app()->getLocale();
    }

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\ProductOptionFactory
     */
    protected static function newFactory(): ProductOptionFactory
    {
        return ProductOptionFactory::new();
    }

    public function getNameAttribute($value)
    {
        return json_decode($value);
    }

    protected function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode($value);
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function values()
    {
        return $this->hasMany(ProductOptionValue::class);
    }

    /**
     * Returns the indexable data for the collection.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        if (config('scout.driver') == 'mysql') {
            return $this->only(array_keys($this->getAttributes()));
        }
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'options' => $this->values->map(function ($option) {
                return $option->translate('name');
            })->toArray(),
        ];
    }
}
