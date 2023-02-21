<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Kalnoy\Nestedset\NodeTrait;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasChannels;
use Lunar\Base\Traits\HasCustomerGroups;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasMedia;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\HasUrls;
use Lunar\Base\Traits\Searchable;
use Lunar\Database\Factories\CollectionFactory;
use Lunar\FieldTypes\TranslatedText;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property int $collection_group_id
 * @property-read  int $_lft
 * @property-read  int $_rgt
 * @property ?int $parent_id
 * @property string $type
 * @property ?array $attribute_data
 * @property string $sort
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class Collection extends BaseModel implements SpatieHasMedia
{
    use HasFactory,
        HasMedia,
        NodeTrait,
        HasTranslations,
        HasChannels,
        HasUrls,
        HasCustomerGroups,
        HasMacros,
        Searchable {
            NodeTrait::usesSoftDelete insteadof Searchable;
        }

    /**
     * Define our base filterable attributes.
     *
     * @var array
     */
    protected $filterable = [];

    /**
     * Define our base sortable attributes.
     *
     * @var array
     */
    protected $sortable = [
        'name',
    ];

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
     * @return \Lunar\Database\Factories\CollectionFactory
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

    public function scopeInGroup(Builder $builder, $id)
    {
        return $builder->where('collection_group_id', $id);
    }

    /**
     * Return the products relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Product::class,
            "{$prefix}collection_product"
        )->withPivot([
            'position',
        ])->withTimestamps()->orderByPivot('position');
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix').'collections';
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

        return $data;
    }

    /**
     * Get the translated name of ancestor collections.
     *
     * @return Illuminate\Support\Collection
     */
    public function getBreadcrumbAttribute()
    {
        return $this->ancestors->map(function ($ancestor) {
            return $ancestor->translateAttribute('name');
        });
    }

    /**
     * Return the customer groups relationship.
     *
     * @return BelongsToMany
     */
    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

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
