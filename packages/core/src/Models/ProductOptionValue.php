<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Database\Factories\ProductOptionValueFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * @return \GetCandy\Database\Factories\ProductOptionValueFactory
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
