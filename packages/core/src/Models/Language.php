<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\LanguageFactory;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $default
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Language extends BaseModel
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): LanguageFactory
    {
        return LanguageFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the URLs relationship
     */
    public function urls(): HasMany
    {
        return $this->hasMany(Url::class);
    }
}
