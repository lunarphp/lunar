<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasPrices;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\BundleFactory;
use Spatie\LaravelBlink\BlinkFacade as Blink;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $tax_class_id
 * @property array $attribute_data
 * @property int $unit_quantity
 * @property int $min_quantity
 * @property int $quantity_increment
 * @property ?string $sku
 * @property ?string $gtin
 * @property ?string $mpn
 * @property ?string $ean
 * @property bool $shippable
 * @property int $stock
 * @property int $backorder
 * @property string $purchasable
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class Bundle extends BaseModel implements Purchasable
{
    use HasAttributes;
    use HasFactory;
    use HasMacros;
    use HasPrices;
    use HasTranslations;
    use LogsActivity;

    /**
     * Define the guarded attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'requires_shipping' => 'bool',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): BundleFactory
    {
        return BundleFactory::new();
    }

    public function bundleable(): MorphTo
    {
        return $this->morphTo();
    }

    public function products(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphedByMany(
            ProductVariant::class,
            'bundleable',
            "{$prefix}bundleables"
        );
    }

    /**
     * Return the tax class relationship.
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * Return the unit quantity for the variant.
     */
    public function getUnitQuantity(): int
    {
        return $this->unit_quantity;
    }

    /**
     * Return the tax class.
     */
    public function getTaxClass(): TaxClass
    {
        return Blink::once("tax_class_{$this->tax_class_id}", function () {
            return $this->taxClass;
        });
    }

    public function getTaxReference(): ?string
    {
        return $this->tax_ref;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->shippable ? 'physical' : 'digital';
    }

    /**
     * {@inheritDoc}
     */
    public function isShippable(): bool
    {
        return $this->shippable;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->product->translateAttribute('name');
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(): void
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return $this->sku;
    }

    public function getThumbnail(): ?Media
    {
        return $this->images->first(function ($media) {
            return (bool) $media->pivot?->primary;
        }) ?: $this->product->thumbnail;
    }
}
