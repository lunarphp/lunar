<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Concerns\HasMacros;

class UserPermission extends Base implements Contracts\UserPermission
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
