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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'admin',
        'email',
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Append attributes to the model.
     *
     * @var array
     */
    protected $appends = [
        'fullName',
    ];

    /**
     * Get staff member's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * Create a new instance of the Model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('lunar.database.table_prefix').$this->getTable());

        if ($connection = config('lunar.database.connection')) {
            $this->setConnection($connection);
        }
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): StaffFactory
    {
        return StaffFactory::new();
    }

    /**
     * Apply the basic search scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $term
     * @return void
     */
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
