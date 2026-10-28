<?php

namespace Lunar\Bundles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Bundles\Database\Factories\BundleComponentFactory;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\InvalidatesRelatedCache;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\ProductVariant;

/**
 * A variant inside a bundle, either always included (no group) or offered as
 * an option in a group.
 *
 * @property int $id
 * @property string $public_id
 * @property int $bundle_id
 * @property ?int $bundle_group_id
 * @property int $product_variant_id
 * @property int $quantity
 * @property bool $default
 * @property int $position
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class BundleComponent extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use InvalidatesRelatedCache;
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'default' => 'boolean',
        'position' => 'integer',
    ];

    protected static function newFactory(): BundleComponentFactory
    {
        return BundleComponentFactory::new();
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(BundleGroup::class, 'bundle_group_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function isFixed(): bool
    {
        return $this->bundle_group_id === null;
    }

    /** @return iterable<Model> */
    public function cacheInvalidationTargets(): iterable
    {
        $this->loadMissing('bundle');

        return $this->bundle ? [$this->bundle, ...$this->bundle->cacheInvalidationTargets()] : [];
    }
}
