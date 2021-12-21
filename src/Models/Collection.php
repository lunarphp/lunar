<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\AsAttributeData;
use GetCandy\Base\Traits\HasChannels;
use GetCandy\Base\Traits\HasCustomerGroups;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Base\Traits\HasUrls;
use GetCandy\Database\Factories\CollectionFactory;
use GetCandy\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

class Collection extends BaseModel implements SpatieHasMedia
{
    use HasFactory,
        HasMedia,
        NodeTrait,
        HasTranslations,
        HasChannels,
        HasUrls,
        HasCustomerGroups,
        Searchable {
            NodeTrait::usesSoftDelete insteadof Searchable;
    }

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attribute_data' => AsAttributeData::class,
    ];

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CollectionFactory
     */
    protected static function newFactory(): CollectionFactory
    {
        return CollectionFactory::new();
    }

    /**
     * Return the group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(CollectionGroup::class, 'collection_group_id');
    }

    /**
     * Return the products relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            Product::class,
            "{$prefix}collection_product"
        )->withPivot([
            'position',
        ])->withTimestamps()->orderByPivot('position');
    }

    /**
     * Returns the indexable data for the collection.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $attributes = $this->getAttributes();

        $data = Arr::except($attributes, 'attribute_data');

        foreach ($this->attribute_data ?? [] as $field => $value) {
            $data[$field] = $this->translateAttribute($field);
        }

        return $data;
    }

    /**
     * Return the customer groups relationship.
     *
     * @return BelongsToMany
     */
    public function customerGroups(): BelongsToMany
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::class,
            "{$prefix}collection_customer_group"
        )->withPivot([
            'visible',
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }
}
