<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Channel
{
    /**
     * Get the parent channelable model.
     */
    public function channelable(): MorphTo;

    /**
     * Return the products relationship
     */
    public function products(): MorphToMany;

    /**
     * Return the products relationship
     */
    public function collections(): MorphToMany;
}
