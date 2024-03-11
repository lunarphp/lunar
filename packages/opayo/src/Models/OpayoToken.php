<?php

namespace Lunar\Opayo\Models;

use Lunar\Base\BaseModel;

/**
 * @property int $id
 * @property int $user_id;
 * @property string $card_type
 * @property string $last_four
 * @property string $token
 * @property ?string $auth_code
 * @property \Illuminate\Support\Carbon $expires_at
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
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
