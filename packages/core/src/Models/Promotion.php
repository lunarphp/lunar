<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Promotion as PromotionContract;
use Lunar\Core\Database\Factories\PromotionFactory;
use Lunar\Core\Models\Builders\Builder;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property Collection $name
 * @property ?Collection $description
 * @property string $handle
 * @property ?Carbon $starts_at
 * @property ?Carbon $ends_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Promotion extends Base implements PromotionContract
{
    use HasFactory;
    use HasPublicId;
    use HasTranslations;
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'name' => AsCollection::class,
        'description' => AsCollection::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function newFactory(): PromotionFactory
    {
        return PromotionFactory::new();
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    /**
     * Scope to campaigns whose window is open: started (or unbounded) and not
     * yet ended (or unbounded). An unwindowed promotion is always active.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }
}
