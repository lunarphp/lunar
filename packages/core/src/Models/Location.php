<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\LocationFactory;
use Lunar\Core\Models\Concerns\HasDefaultRecord;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;
use Spatie\LaravelBlink\BlinkFacade as Blink;

/**
 * A physical place — a warehouse or store — that fulfilments are assigned to
 * and (in future) that inventory is tracked against.
 *
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $handle
 * @property bool $default
 * @property bool $accepts_pickup
 * @property ?string $line_one
 * @property ?string $line_two
 * @property ?string $line_three
 * @property ?string $city
 * @property ?string $state
 * @property ?string $postcode
 * @property ?int $country_id
 * @property ?string $contact_phone
 * @property ?array $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Location extends Base
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'default' => 'boolean',
        'accepts_pickup' => 'boolean',
        'meta' => AsArrayObject::class,
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Blink::forget('lunar_default_pickup_location'));
        static::deleted(fn () => Blink::forget('lunar_default_pickup_location'));
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return LocationFactory::new();
    }

    /**
     * Mutator for formatting the handle to a slug.
     */
    public function setHandleAttribute(?string $val): void
    {
        $this->attributes['handle'] = Str::slug($val);
    }

    /**
     * Locations customers can collect from.
     */
    public function scopeAcceptsPickup(Builder $query): void
    {
        $query->where('accepts_pickup', true);
    }

    /**
     * Where a pickup is collected when nothing chooses a location: the
     * default location when it accepts pickup, otherwise the first location
     * that does by name, otherwise null.
     */
    public static function defaultPickup(): ?self
    {
        return Blink::once('lunar_default_pickup_location', fn () => static::query()
            ->acceptsPickup()
            ->orderByDesc('default')
            ->orderBy('name')
            ->first());
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Return the fulfilments relationship.
     */
    public function fulfilments(): HasMany
    {
        return $this->hasMany(Fulfilment::class);
    }
}
