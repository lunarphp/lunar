<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Kalnoy\Nestedset\NodeTrait;
use Kalnoy\Nestedset\QueryBuilder;
use Lunar\Core\Contracts\HasThumbnailImage;
use Lunar\Core\Database\Factories\CollectionFactory;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasChannels;
use Lunar\Core\Models\Concerns\HasCustomerGroups;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\HasUrls;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Core\States\Collection\CollectionState;
use Lunar\Core\States\Collection\Published;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $collection_group_id
 * @property-read  int $_lft
 * @property-read  int $_rgt
 * @property ?int $parent_id
 * @property string $type
 * @property \Illuminate\Support\Collection $name
 * @property ?\Illuminate\Support\Collection $description
 * @property ?\Illuminate\Support\Collection $short_description
 * @property ?\Illuminate\Support\Collection $attribute_data
 * @property string $sort
 * @property CollectionState $status
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Collection extends Base implements Contracts\Collection, HasThumbnailImage, SpatieHasMedia
{
    use HasAttributeData,
        HasChannels,
        HasCustomerGroups,
        HasFactory,
        HasMacros,
        HasMedia,
        HasStates,
        HasTranslations,
        HasUrls,
        NodeTrait,
        Searchable {
            NodeTrait::usesSoftDelete insteadof Searchable;
        }

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsCollection::class,
        'description' => AsCollection::class,
        'short_description' => AsCollection::class,
        'status' => CollectionState::class,
    ];

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CollectionFactory::new();
    }

    public function getScopeAttributes()
    {
        return ['collection_group_id'];
    }

    /**
     * Return the group relationship.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CollectionGroup::class, 'collection_group_id');
    }

    public function scopeInGroup(Builder $builder, int $id): Builder
    {
        return $builder->where('collection_group_id', $id);
    }

    public function scopeWhereVisible(Builder $builder): Builder
    {
        return $builder->where('status', Published::$name);
    }

    /**
     * Return the products relationship.
     */
    public function products(): BelongsToMany
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
     * Get the translated name of ancestor collections.
     */
    public function getBreadcrumbAttribute(): \Illuminate\Support\Collection
    {
        return $this->ancestors->map(function ($ancestor) {
            return $ancestor->translate('name');
        });
    }

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

    public function discounts(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Discount::class,
            "{$prefix}collection_discount"
        )->withPivot(['type'])->withTimestamps();
    }

    public function brands(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Brand::class,
            "{$prefix}brand_collection"
        )->withTimestamps();
    }

    public function newEloquentBuilder($query): QueryBuilder
    {
        return new QueryBuilder($query);
    }

    public function getThumbnailImage(): string
    {
        return $this->thumbnail?->getUrl('small') ?? '';
    }
}
