<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\AsAttributeData;
use GetCandy\Base\Traits\HasChannels;
use GetCandy\Base\Traits\HasCustomerGroups;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasTags;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Base\Traits\HasUrls;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Base\Traits\Searchable;
use GetCandy\Database\Factories\ProductFactory;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Jobs\Products\Associations\Associate;
use GetCandy\Jobs\Products\Associations\Dissociate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

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
     *
     * @return \GetCandy\Database\Factories\ProductFactory
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
            config('getcandy.database.table_prefix').'collection_product'
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customerGroups(): BelongsToMany
    {
        $prefix = config('getcandy.database.table_prefix');

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
}
