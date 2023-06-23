<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
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
use Lunar\FieldTypes\TranslatedText;
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
class Product extends BaseModel implements SpatieHasMedia
{
    use HasFactory;
    use HasMedia;
    use LogsActivity;
    use HasChannels;
    use HasTranslations;
    use HasTags;
    use HasCustomerGroups;
    use HasUrls;
    use Searchable;
    use SoftDeletes;
    use HasMacros;

    /**
     * Define our base filterable attributes.
     *
     * @var array
     */
    protected $filterable = [
        '__soft_deleted',
        'skus',
        'status',
    ];

    /**
     * Define our base sortable attributes.
     *
     * @var array
     */
    protected $sortable = [
        'name',
        'created_at',
        'updated_at',
        'skus',
        'status',
    ];

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix').'products';
    }

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
     * Returns the attributes to be stored against this model.
     *
     * @return array
     */
    public function mappedAttributes()
    {
        return $this->productType->mappedAttributes;
    }

    /**
     * Return the product type relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Return the product images relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->media()->where('collection_name', 'images');
    }

    /**
     * Return the product variants relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Return the product collections relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collections()
    {
        return $this->belongsToMany(
            Collection::class,
            config('lunar.database.table_prefix').'collection_product'
        )->withPivot(['position'])->withTimestamps();
    }

    /**
     * Return the associations relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function associations()
    {
        return $this->hasMany(ProductAssociation::class, 'product_parent_id');
    }

    /**
     * Return the associations relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inverseAssociations()
    {
        return $this->hasMany(ProductAssociation::class, 'product_target_id');
    }

    /**
     * Associate a product to another with a type.
     *
     * @param  mixed  $product
     * @param  string  $type
     * @return void
     */
    public function associate($product, $type)
    {
        Associate::dispatch($this, $product, $type);
    }

    /**
     * Dissociate a product to another with a type.
     *
     * @param  mixed  $product
     * @param  string  $type
     * @return void
     */
    public function dissociate($product, $type = null)
    {
        Dissociate::dispatch($this, $product, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchableAttributes()
    {
        $attributes = $this->getAttributes();

        $data = Arr::except($attributes, 'attribute_data');

        foreach ($this->attribute_data ?? [] as $field => $value) {
            if ($value instanceof TranslatedText) {
                foreach ($value->getValue() as $locale => $text) {
                    $data[$field.'_'.$locale] = $text?->getValue();
                }
            } else {
                $data[$field] = $this->translateAttribute($field);
            }
        }

        if ($this->thumbnail) {
            $data['thumbnail'] = $this->thumbnail->getUrl('small');
        }

        $data['skus'] = $this->variants()->pluck('sku')->toArray();

        return $data;
    }

    /**
     * Return the customer groups relationship.
     */
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

    /**
     * Return the brand relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Apply the status scope.
     *
     * @param  string  $status
     * @return Builder
     */
    public function scopeStatus(Builder $query, $status)
    {
        return $query->whereStatus($status);
    }

    /**
     * Return the prices relationship.
     *
     * @return HasManyThrough
     */
    public function prices()
    {
        return $this->hasManyThrough(
            Price::class,
            ProductVariant::class,
            'product_id',
            'priceable_id'
        )->wherePriceableType(ProductVariant::class);
    }
}
