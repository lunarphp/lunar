<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasChannels;
use Lunar\Base\Traits\HasCustomerGroups;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTags;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\HasUrls;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\Traits\Searchable;
use Lunar\Database\Factories\ProductFactory;
use Lunar\Jobs\Products\Associations\Associate;
use Lunar\Jobs\Products\Associations\Dissociate;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property ?int $brand_id
 * @property int $product_type_id
 * @property string $status
 * @property array $attribute_data
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class Product extends BaseModel implements SpatieHasMedia, \Lunar\Models\Contracts\Product
{
    use HasChannels;
    use HasCustomerGroups;
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasTags;
    use HasTranslations;
    use HasUrls;
    use LogsActivity;
    use Searchable;
    use SoftDeletes;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    /**
     * Define which attributes should be
     * fillable during mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_data',
        'product_type_id',
        'status',
        'brand_id',
    ];

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attribute_data' => AsAttributeData::class,
    ];

    /**
     * Record's title
     */
    protected function recordTitle(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->translateAttribute('name'),
        );
    }

    public function mappedAttributes(): Collection
    {
        return $this->productType->mappedAttributes;
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::modelClass());
    }

    public function images(): MorphMany
    {
        return $this->media()->where('collection_name', 'images');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::modelClass());
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(
            \Lunar\Models\Collection::modelClass(),
            config('lunar.database.table_prefix').'collection_product'
        )->withPivot(['position'])->withTimestamps();
    }

    public function associations(): HasMany
    {
        return $this->hasMany(ProductAssociation::modelClass(), 'product_parent_id');
    }

    public function inverseAssociations(): HasMany
    {
        return $this->hasMany(ProductAssociation::modelClass(), 'product_target_id');
    }

    public function associate(mixed $product, string $type): void
    {
        Associate::dispatch($this, $product, $type);
    }

    public function dissociate(mixed $product, string $type = null): void
    {
        Dissociate::dispatch($this, $product, $type);
    }

    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::modelClass(),
            "{$prefix}customer_group_product"
        )->withPivot([
            'purchasable',
            'visible',
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::modelClass());
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->whereStatus($status);
    }

    public function prices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Price::modelClass(),
            ProductVariant::modelClass(),
            'product_id',
            'priceable_id'
        )->wherePriceableType('product_variant');
    }

    public function productOptions(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            ProductOption::modelClass(),
            "{$prefix}product_product_option"
        )->withPivot(['position'])->orderByPivot('position');
    }
}
