<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\Searchable;
use Lunar\Database\Factories\ProductOptionFactory;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

class ProductOption extends BaseModel implements SpatieHasMedia
{
    use HasFactory;
    use HasMedia;
    use HasTranslations;
    use Searchable;
    use HasMacros;

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
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsCollection::class,
    ];

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix').'product_options';
    }

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\ProductOptionFactory
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
        return $this->hasMany(ProductOptionValue::class)->orderBy('position');
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchableAttributes()
    {
        $data['id'] = $this->id;

        // Loop for add option name
        foreach ($this->name as $locale => $name) {
            $data['name_'.$locale] = $name;
        }

        // Loop for add options
        foreach ($this->values as $option) {
            foreach ($option->name as $locale => $name) {
                $key = 'option_'.$option->id.'_'.$locale;
                $data[$key] = $name;
            }
        }

        return $data;
    }
}
