<?php

namespace Lunar\SearchRelevance\Models;

use Illuminate\Support\Carbon;
use Lunar\Core\Models\Base;

/**
 * A staff decision about what scoring may learn: `exclude` keeps one product
 * out of a query's learned list, `reset` discards everything learned for a
 * query before the override was created.
 *
 * @property int $id
 * @property string $model_type
 * @property string $normalised_query
 * @property ?int $product_id
 * @property string $type
 * @property Carbon $created_at
 */
class LearningOverride extends Base
{
    public const EXCLUDE = 'exclude';

    public const RESET = 'reset';

    protected $table = 'search_learning_overrides';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
