<?php

namespace Lunar\Tests\Core\Stubs;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * An app model that has media through Spatie directly, not through Lunar.
 */
class NonLunarMediaOwner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'users';

    protected $guarded = [];
}
