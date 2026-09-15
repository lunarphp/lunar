<?php

namespace Lunar\Bundles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Contracts\Actions\ResolvesBundleSelection;
use Lunar\Bundles\Contracts\Actions\SyncsBundleComponents;
use Lunar\Bundles\Contracts\Actions\SyncsBundleGroups;
use Lunar\Bundles\Database\Factories\BundleFactory;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Events\BundleInvalidated;
use Lunar\Bundles\ValueObjects\BundleSelection;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\InvalidatesCache;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\ProductVariant;

/**
 * The component list attached to a product variant. The variant stays an
 * ordinary variant; this row is what makes it a bundle.
 *
 * @property int $id
 * @property string $public_id
 * @property int $product_variant_id
 * @property BundlePricing $pricing
 * @property ?float $discount_percentage
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Bundle extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use InvalidatesCache;
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'pricing' => BundlePricing::class,
        'discount_percentage' => 'float',
    ];

    protected static function newFactory(): BundleFactory
    {
        return BundleFactory::new();
    }

    /** The bundle variant: the sellable unit that carries the components. */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(BundleGroup::class)->orderBy('position')->orderBy('id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(BundleComponent::class)->with('group')->orderBy('position')->orderBy('id');
    }

    public function fixedComponents(): HasMany
    {
        return $this->hasMany(BundleComponent::class)->whereNull('bundle_group_id')->orderBy('position')->orderBy('id');
    }

    /** A bundle with at least one group is configurable; with none it is fixed. */
    public function isConfigurable(): bool
    {
        return $this->loadMissing('groups')->groups->isNotEmpty();
    }

    /**
     * A bundle has no cacheable page of its own; its product does.
     *
     * @return iterable<Model>
     */
    public function cacheInvalidationTargets(): iterable
    {
        $this->loadMissing('variant.product');

        return array_values(array_filter([$this->variant?->product]));
    }

    public function newCacheInvalidationEvent(CacheInvalidationReason $reason): CacheInvalidationEvent
    {
        return new BundleInvalidated($this, $reason);
    }

    /**
     * @param  list<array{variant: ProductVariant|int, quantity: int, group?: BundleGroup|int|null, default?: bool, position?: int}>  $components
     */
    public function syncComponents(array $components): self
    {
        return app(SyncsBundleComponents::class)->execute($this, $components);
    }

    /**
     * @param  list<array{id?: int, name: array<string, string>, min_selections: int, max_selections: int, position?: int}>  $groups
     */
    public function syncGroups(array $groups): self
    {
        return app(SyncsBundleGroups::class)->execute($this, $groups);
    }

    public function reprice(): self
    {
        return app(RepricesBundle::class)->execute($this);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function resolveSelection(array $meta = []): BundleSelection
    {
        return app(ResolvesBundleSelection::class)->execute($this, $meta);
    }
}
