<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\ProductOptionFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasMedia;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

/**
 * @property int $id
 * @property AsArrayObject $name
 * @property ?AsArrayObject $label
 * @property int $position
 * @property ?string $handle
 * @property bool $shared
 * @property ?AsArrayObject $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductOption extends Base implements SpatieHasMedia
{
    use HasFactory;
    use HasMacros;
    use HasMedia;
    use HasTranslations;
    use LogsActivity;
    use Searchable;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => AsArrayObject::class,
        'label' => AsArrayObject::class,
        'shared' => 'boolean',
        'meta' => AsArrayObject::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductOptionFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function scopeShared(Builder $builder): Builder
    {
        return $builder->where('shared', '=', true);
    }

    public function scopeExclusive(Builder $builder): Builder
    {
        return $builder->where('shared', '=', false);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class)->orderBy('position');
    }

    public function products(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Product::class,
            "{$prefix}product_product_option"
        )->withPivot(['position'])->orderByPivot('position');
    }
}
