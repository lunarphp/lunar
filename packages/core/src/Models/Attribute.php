<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Database\Factories\AttributeFactory;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * @property int $id
 * @property ?int $attribute_group_id
 * @property string $name
 * @property string $handle
 * @property string $type
 * @property ?Collection $configuration
 * @property int $position
 * @property bool $required
 * @property bool $searchable
 * @property bool $filterable
 * @property bool $system
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Attribute extends Base
{
    use HasFactory;
    use HasMacros;

    protected static function booted(): void
    {
        static::deleting(function (self $attribute) {
            $prefix = config('lunar.database.table_prefix');

            $attribute->models()->delete();

            $attribute->getConnection()
                ->table($prefix.'product_type_attribute')
                ->where('attribute_id', $attribute->id)
                ->delete();
        });
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return AttributeFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'configuration' => AsCollection::class,
        'required' => 'bool',
        'searchable' => 'bool',
        'filterable' => 'bool',
        'system' => 'bool',
    ];

    public function setHandleAttribute(string $value): void
    {
        $this->attributes['handle'] = Str::slug($value, '_');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(AttributeModel::class);
    }

    /**
     * Resolve the field type instance for this attribute.
     */
    public function fieldType(): FieldType
    {
        $class = FieldTypeManifest::getType($this->type);

        return new $class;
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('system', true);
    }
}
