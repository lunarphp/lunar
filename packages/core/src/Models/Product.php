<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Contracts\HasThumbnailImage;
use Lunar\Core\Database\Factories\ProductFactory;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Events\Catalog\ProductInvalidated;
use Lunar\Core\Jobs\Products\Associations\Associate;
use Lunar\Core\Jobs\Products\Associations\Dissociate;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasChannels;
use Lunar\Core\Models\Concerns\HasCustomerGroups;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\HasTags;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\HasUrls;
use Lunar\Core\Models\Concerns\InvalidatesCache;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Core\States\Product\ProductState;
use Lunar\Core\States\Product\Published;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $public_id
 * @property ?int $brand_id
 * @property int $product_type_id
 * @property ProductState $status
 * @property \Illuminate\Support\Collection $name
 * @property ?\Illuminate\Support\Collection $description
 * @property ?\Illuminate\Support\Collection $short_description
 * @property ?\Illuminate\Support\Collection $attribute_data
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Product extends Base implements HasThumbnailImage, SpatieHasMedia
{
    use HasAttributeData;
    use HasChannels;
    use HasCustomerGroups;
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasPublicId;
    use HasStates;
    use HasTags;
    use HasTranslations;
    use HasUrls;
    use InvalidatesCache;
    use LogsActivity;
    use Searchable;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
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
        'public_id',
        'product_type_id',
        'status',
        'brand_id',
        'name',
        'description',
        'short_description',
    ];

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsCollection::class,
        'description' => AsCollection::class,
        'short_description' => AsCollection::class,
        'status' => ProductState::class,
    ];

    /**
     * Record's title
     */
    protected function recordTitle(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->translate('name'),
        );
    }

    public function mappedAttributes(): Collection
    {
        return $this->productType->attributeMapping;
    }

    public function newCacheInvalidationEvent(CacheInvalidationReason $reason): CacheInvalidationEvent
    {
        return new ProductInvalidated($this, $reason);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function images(): MorphMany
    {
        return $this->media()->where('collection_name', config('lunar.media.collection'));
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Scoped route bindings guess a `productVariants` relation from the
     * `{productVariant}` parameter; the relation is named `variants`.
     */
    protected function childRouteBindingRelationshipName($childType): string
    {
        return $childType === 'productVariant'
            ? 'variants'
            : parent::childRouteBindingRelationshipName($childType);
    }

    public function variant(): HasOne
    {
        return $this->hasOne(ProductVariant::class);
    }

    protected function hasVariants(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants()->count() > 1,
        );
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(
            \Lunar\Core\Models\Collection::class,
            config('lunar.database.table_prefix').'collection_product'
        )->withPivot(['position'])->orderByPivot('position')->withTimestamps();
    }

    public function associations(): HasMany
    {
        return $this->hasMany(ProductAssociation::class, 'product_parent_id')
            ->orderBy('sort')->orderBy('id');
    }

    public function inverseAssociations(): HasMany
    {
        return $this->hasMany(ProductAssociation::class, 'product_target_id');
    }

    public function associate(mixed $product, ProvidesProductAssociationType|string $type): void
    {
        Associate::dispatch($this, $product, $type);
    }

    /**
     * Dissociate a product to another with a type.
     */
    public function dissociate(mixed $product, ProvidesProductAssociationType|string|null $type = null): void
    {
        Dissociate::dispatch($this, $product, $type);
    }

    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::class,
            "{$prefix}customer_group_product"
        )->withPivot([
            'purchasable',
            'visible',
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }

    public static function getExtraCustomerGroupPivotValues(CustomerGroup $customerGroup): array
    {
        return [
            'purchasable' => $customerGroup->default,
        ];
    }

    /**
     * Return the brand relationship.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->whereStatus($status);
    }

    public function scopeWhereVisible(Builder $query): Builder
    {
        return $query->where('status', Published::$name);
    }

    public function prices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Price::class,
            ProductVariant::class,
            'product_id',
            'priceable_id'
        )->wherePriceableType('product_variant');
    }

    public function productOptions(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            ProductOption::class,
            "{$prefix}product_product_option"
        )->withPivot(['position'])->orderByPivot('position');
    }

    public function getThumbnailImage(): string
    {
        return $this->thumbnail?->getUrl('small') ?? '';
    }

    /**
     * Whether any of this product's variants appear on any historical order line.
     * Used to gate hard deletion in the admin — products with order history
     * should be archived, not deleted, so the merchant can still drill into
     * old orders.
     */
    public function hasOrderHistory(): bool
    {
        $variantClass = ProductVariant::class;

        return OrderLine::query()
            ->where('purchasable_type', (new $variantClass)->getMorphClass())
            ->whereIn('purchasable_id', $this->variants()->select('id'))
            ->exists();
    }
}
