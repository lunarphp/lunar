<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Database\Factories\AttributeModelFactory;

/**
 * @property int $id
 * @property int $attribute_id
 * @property string $model_type
 */
class AttributeModel extends Base implements Contracts\AttributeModel
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return AttributeModelFactory::new();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
