<?php

namespace Lunar\Hub\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;

class StaffPermission extends BaseModel
{
    protected $fillable = ['handle'];

    /**
     * Return the staff relationship.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
