<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends BaseModel
{
    use HasFactory;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CountryFactory
     */
    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the states relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }
}
