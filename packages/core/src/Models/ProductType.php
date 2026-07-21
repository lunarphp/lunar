<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\ProductTypeFactory;
use Lunar\Core\Exceptions\ProductTypeActionException;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\States\ProductType\Active;
use Lunar\Core\States\ProductType\ProductTypeState;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $handle
 * @property ProductTypeState $status
 * @property ?string $description
 * @property ?int $default_tax_class_id
 * @property ?array $attribute_data
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductType extends Base implements SpatieHasMedia
{
    use HasAttributeData;
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasPublicId;
    use HasStates;
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

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'status' => ProductTypeState::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $productType) {
            if (blank($productType->handle)) {
                $productType->handle = static::uniqueHandle($productType->name);
            }
        });

        // A replica must not clone the unique handle; clearing it lets the
        // creating hook mint a fresh suffixed one.
        static::replicating(function (self $productType) {
            $productType->handle = null;
        });

        // The guard lives on the model, not just the admin actions, so every
        // delete path (Eloquent, bulk actions, consumer code) refuses while
        // products still reference the type — reassign or remove them first.
        // Without it the products FK surfaces as a raw QueryException.
        static::deleting(function (self $productType) {
            if ($productType->products()->exists()) {
                throw new ProductTypeActionException(
                    'Product type has products — reassign or remove them before deleting.'
                );
            }

            $productType->attributeMapping()->detach();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Active::$name);
    }

    /**
     * Generate a kebab-case handle from the name, suffixed until unique:
     * product-type, product-type-2, product-type-3, ...
     */
    protected static function uniqueHandle(string $name): string
    {
        $base = Str::slug($name) ?: 'product-type';
        $handle = $base;

        for ($suffix = 2; static::where('handle', $handle)->exists(); $suffix++) {
            $handle = $base.'-'.$suffix;
        }

        return $handle;
    }

    /**
     * The attributes this type maps onto its products and variants — distinct
     * from `mappedAttributes()` (via HasAttributeData), which is the type's
     * own fields.
     */
    public function attributeMapping(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Attribute::class,
            "{$prefix}product_type_attribute",
        )->withTimestamps();
    }

    public function productAttributes(): BelongsToMany
    {
        return $this->attributeMapping()->whereHas(
            'models',
            fn ($query) => $query->where('model_type', Product::morphName())
        );
    }

    public function variantAttributes(): BelongsToMany
    {
        return $this->attributeMapping()->whereHas(
            'models',
            fn ($query) => $query->where('model_type', ProductVariant::morphName())
        );
    }

    public function defaultTaxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'default_tax_class_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
