<?php

namespace Lunar\Models;

use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends BaseModel
{
    use HasMacros;

    protected $fillable = ['handle'];

    /**
     * Return the user relationship.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
