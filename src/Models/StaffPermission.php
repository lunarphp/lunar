<?php

namespace GetCandy\Hub\Models;

use GetCandy\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPermission extends BaseModel
{
    protected $fillable = ['handle'];

    /**
     * Return the staff relationship.
     *
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
