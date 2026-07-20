<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Database\Factories\BrandFactory;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Events\Catalog\BrandInvalidated;
use Lunar\Core\Exceptions\BrandActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\HasUrls;
use Lunar\Core\Models\Concerns\InvalidatesCache;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Core\States\Brand\Active;
use Lunar\Core\States\Brand\BrandState;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $handle
 * @property BrandState $status
 * @property ?\Illuminate\Support\Collection $description
 * @property ?\Illuminate\Support\Collection $short_description
 * @property ?array $attribute_data
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Brand extends Base implements SpatieHasMedia
{
    use HasAttributeData;
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasPublicId;
    use HasStates;
    use HasTranslations;
    use HasUrls;
    use InvalidatesCache;
    use LogsActivity;
    use Searchable;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'status' => BrandState::class,
        'description' => AsCollection::class,
        'short_description' => AsCollection::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return BrandFactory::new();
    }

    public function newCacheInvalidationEvent(CacheInvalidationReason $reason): CacheInvalidationEvent
    {
        return new BrandInvalidated($this, $reason);
    }

    protected static function booted(): void
    {
        static::creating(function (self $brand) {
            if (blank($brand->handle)) {
                $brand->handle = static::uniqueHandle($brand->name);
            }
        });

        // A replica must not clone the unique handle; clearing it lets the
        // creating hook mint a fresh suffixed one.
        static::replicating(function (self $brand) {
            $brand->handle = null;
        });

        // The guard lives on the model, not just the admin actions, so every
        // delete path (Eloquent, bulk actions, consumer code) refuses while
        // products still reference the brand — reassign or remove them first.
        static::deleting(function (self $brand) {
            if ($brand->products()->exists()) {
                throw new BrandActionException(
                    'Brand has products — reassign or remove them before deleting.'
                );
            }

            DB::beginTransaction();
            $brand->discounts()->detach();
            $brand->collections()->detach();
            DB::commit();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Active::$name);
    }

    /**
     * Generate a kebab-case handle from the name, suffixed until unique:
     * brand, brand-2, brand-3, ...
     */
    protected static function uniqueHandle(string $name): string
    {
        $base = Str::slug($name) ?: 'brand';
        $handle = $base;

        for ($suffix = 2; static::where('handle', $handle)->exists(); $suffix++) {
            $handle = $base.'-'.$suffix;
        }

        return $handle;
    }

    /**
     * Return the product relationship.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function discounts()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Discount::class, "{$prefix}brand_discount");
    }

    public function collections(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Collection::class, "{$prefix}brand_collection");
    }
}
