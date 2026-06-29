<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\LanguageFactory;
use Lunar\Core\Models\Concerns\HasDefaultRecord;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $default
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Language extends Base
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
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

    public function urls(): HasMany
    {
        return $this->hasMany(Url::class);
    }
}
