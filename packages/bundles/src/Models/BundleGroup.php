<?php

namespace Lunar\Bundles\Models;

use ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Bundles\Database\Factories\BundleGroupFactory;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\InvalidatesRelatedCache;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * A choice set inside a configurable bundle: "pick between min and max of
 * these components".
 *
 * @property int $id
 * @property string $public_id
 * @property int $bundle_id
 * @property ArrayObject $name
 * @property int $min_selections
 * @property int $max_selections
 * @property int $position
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class BundleGroup extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use HasTranslations;
    use InvalidatesRelatedCache;
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'name' => AsArrayObject::class,
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'position' => 'integer',
    ];

    protected static function newFactory(): BundleGroupFactory
    {
        return BundleGroupFactory::new();
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(BundleComponent::class)->orderBy('position')->orderBy('id');
    }

    /** @return iterable<Model> */
    public function cacheInvalidationTargets(): iterable
    {
        $this->loadMissing('bundle');

        return $this->bundle ? [$this->bundle, ...$this->bundle->cacheInvalidationTargets()] : [];
    }
}
