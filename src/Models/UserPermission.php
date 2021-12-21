<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends BaseModel
{
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
