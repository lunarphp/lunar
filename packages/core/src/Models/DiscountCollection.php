<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Database\Factories\DiscountableFactory;

class DiscountCollection extends Base implements Contracts\DiscountCollection
{
    use HasFactory;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return DiscountableFactory::new();
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
