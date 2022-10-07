<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Database\Factories\ProductOptionValueFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

class ProductOptionValue extends BaseModel implements SpatieHasMedia
{
    use HasFactory;
    use HasMedia;
    use HasTranslations;
    use HasMacros;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsCollection::class,
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\ProductOptionValueFactory
     */
    protected static function newFactory(): ProductOptionValueFactory
    {
        return ProductOptionValueFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode($value);
    }

    public function getNameAttribute($value)
    {
        return json_decode($value);
    }

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }
}
