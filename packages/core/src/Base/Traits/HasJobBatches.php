<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Lunar\Models\JobBatch;

trait HasJobBatches
{
    /**
     * Get all the model job batches.
     */
    public function jobBatches(): MorphMany
    {
        return $this->morphMany(
            JobBatch::class,
            'subject'
        );
    }

}
