<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Base\Traits\Searchable;
use GetCandy\Database\Factories\ProductOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductOption extends BaseModel
{
    use HasFactory;
    use HasMedia;
    use HasTranslations;
    use Searchable;

    /**
     * Define our base filterable attributes.
     *
     * @var array
     */
    protected $filterable = [];

    /**
     * Define our base sortable attributes.
     *
     * @var array
     */
    protected $sortable = [
        'name',
    ];

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix').'product_options_'.app()->getLocale();
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
     * {@inheritDoc}
     */
    public function getSearchableAttributes()
    {
        return [
            'id'      => $this->id,
            'name'    => collect(json_decode($this->attributes['name']))->values()->all(),
            'options' => $this->values->map(function ($option) {
                return collect(json_decode($option->attributes['name']))->values();
            })->collapse()->toArray(),
        ];
    }
}
