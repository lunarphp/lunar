<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Models\Concerns\HasMacros;

class UserPermission extends Base
{
    use HasMacros;

    protected $fillable = ['handle'];

    /**
     * Return the user relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
