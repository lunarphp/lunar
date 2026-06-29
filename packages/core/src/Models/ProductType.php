<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\ProductTypeFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductType extends Base implements Contracts\ProductType
{
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductTypeFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function mappedAttributes(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Attribute::class,
            "{$prefix}product_type_attribute",
        )->withTimestamps();
    }

    public function productAttributes(): BelongsToMany
    {
        return $this->mappedAttributes()->whereHas(
            'models',
            fn ($query) => $query->where('model_type', Product::morphName())
        );
    }

    public function variantAttributes(): BelongsToMany
    {
        return $this->mappedAttributes()->whereHas(
            'models',
            fn ($query) => $query->where('model_type', ProductVariant::morphName())
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
