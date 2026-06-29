<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\BrandFactory;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\HasUrls;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property string $name
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
    use HasTranslations;
    use HasUrls;
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

    protected static function booted(): void
    {
        static::deleting(function (self $brand) {
            DB::beginTransaction();
            $brand->products()->update(['brand_id' => null]);
            $brand->discounts()->detach();
            $brand->collections()->detach();
            DB::commit();
        });
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
