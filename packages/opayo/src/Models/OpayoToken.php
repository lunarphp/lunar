<?php

namespace Lunar\Opayo\Models;

use Illuminate\Support\Carbon;
use Lunar\Base\BaseModel;

/**
 * @property int $id
 * @property int $user_id;
 * @property string $card_type
 * @property string $last_four
 * @property string $token
 * @property ?string $auth_code
 * @property Carbon $expires_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class OpayoToken extends BaseModel
{
    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
