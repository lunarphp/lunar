<?php

namespace Lunar\Hub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lunar\Hub\Database\Factories\StaffFactory;

class Staff extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Hub\Database\Factories\StaffFactory
     */
    protected static function newFactory(): StaffFactory
    {
        return StaffFactory::new();
    }

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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Create a new instance of the Model.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('lunar.database.table_prefix').$this->getTable());

        if ($connection = config('lunar.database.connection', false)) {
            $this->setConnection($connection);
        }
    }

    /**
     * Retrieve the model for a bound value.
     *
     * Currently Livewire doesn't support route bindings for
     * soft deleted models so we need to rewire it here.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveSoftDeletableRouteBinding($value, $field);
    }

    /**
     * Return the user permissions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(StaffPermission::class);
    }

    /**
     * Authorize an action via permissions.
     *
     * @param  string  $permission
     * @return bool
     */
    public function authorize($permission): bool
    {
        if (! is_array($permission)) {
            $permission = [$permission];
        }

        return $this->permissions()->whereIn('handle', $permission)->exists();
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
                $query->where('email', 'LIKE', "%$part%")
                        ->orWhere('firstname', 'LIKE', "%$part%")
                        ->orWhere('lastname', 'LIKE', "%$part%");
            }
        }
    }

    /**
     * Get staff member's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * Get staff member's Gravatar URLs.
     *
     * @return string
     */
    public function getGravatarAttribute()
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));

        return "https://www.gravatar.com/avatar/$hash?d=mp";
    }

    /**
     * Return the saved searches relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }
}
