<?php

namespace GetCandy\Hub\Models;

use GetCandy\Base\BaseModel;

class SavedSearch extends BaseModel
{
    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'filters' => 'array',
    ];

    /**
     * Return the staff member relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
