<?php

namespace Lunar\Admin\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lunar\Admin\Database\Factories\StaffFactory;
use Spatie\Permission\Traits\HasRoles;

class Staff extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $guard_name = 'staff';

    protected $fillable = [
        'firstname',
        'lastname',
        'admin',
        'email',
        'password',
    ];

    protected $casts = [
        'admin' => 'bool',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'fullName',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('lunar.database.table_prefix').$this->getTable());

        if ($connection = config('lunar.database.connection')) {
            $this->setConnection($connection);
        }
    }

    protected static function newFactory(): StaffFactory
    {
        return StaffFactory::new();
    }

    public function scopeSearch($query, $term)
    {
        if ($term) {
            $parts = explode(' ', $term);

            foreach ($parts as $part) {
                $query->whereAny(['email', 'firstname', 'lastname'], 'LIKE', "%$part%");
            }
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        return $this->fullName;
    }
}
