<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionProduct extends BaseModel
{
    /**
     * The collection this belongs to.
     *
     * @return BelongsTo
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * The product associated with this row.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
