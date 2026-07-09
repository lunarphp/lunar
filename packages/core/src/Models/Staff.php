<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\StaffFactory;
use Lunar\Core\Models\Builders\Builder;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $public_id
 * @property bool $admin
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property ?string $app_authentication_secret
 * @property ?array $app_authentication_recovery_codes
 * @property ?Carbon $email_verified_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @method static Builder search(?string $terms)
 */
class Staff extends Authenticatable
{
    use HasFactory;
    use HasPublicId;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    protected $guard_name = 'staff';

    protected $fillable = [
        'first_name',
        'last_name',
        'admin',
        'email',
        'password',
    ];

    protected $casts = [
        'admin' => 'bool',
        'app_authentication_recovery_codes' => 'encrypted:array',
        'app_authentication_secret' => 'encrypted',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'app_authentication_recovery_codes',
        'app_authentication_secret',
        'password',
        'remember_token',
    ];

    protected $appends = [
        'full_name',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('lunar.database.table_prefix').'staff');

        if ($connection = config('lunar.database.connection')) {
            $this->setConnection($connection);
        }
    }

    protected static function newFactory(): StaffFactory
    {
        return StaffFactory::new();
    }

    /**
     * Staff is not a Base subclass, so give it Lunar's builder explicitly —
     * the HasPublicId `wherePublicId()` scope type-hints it.
     *
     * @return Builder<static>
     */
    public function newEloquentBuilder($query): EloquentBuilder
    {
        return new Builder($query);
    }

    protected function firstname(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'],
            set: fn (string $value) => ['first_name' => $value],
        );
    }

    protected function lastname(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['last_name'],
            set: fn (string $value) => ['last_name' => $value],
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(
            fn (): string => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function scopeSearch(Builder $query, ?string $terms): void
    {
        if (! $terms) {
            return;
        }

        foreach (explode(' ', $terms) as $term) {
            $query->whereAny(['email', 'first_name', 'last_name'], 'LIKE', "%{$term}%");
        }
    }
}
