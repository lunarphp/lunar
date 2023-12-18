<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\Searchable;
use Lunar\Database\Factories\ProductOptionFactory;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property \Illuminate\Support\Collection $name
 * @property \Illuminate\Support\Collection $label
 * @property int $position
 * @property ?string $handle
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class ProductOption extends BaseModel implements SpatieHasMedia
{
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasTranslations;
    use Searchable;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsCollection::class,
        'label' => AsCollection::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): ProductOptionFactory
    {
        return ProductOptionFactory::new();
    }

    public function getNameAttribute(string $value): mixed
    {
        return json_decode($value);
    }

    protected function setNameAttribute(mixed $value): void
    {
        $this->attributes['name'] = json_encode($value);
    }

    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the values.
     *
     * @return HasMany<ProductOptionValue>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class)->orderBy('position');
    }
}
