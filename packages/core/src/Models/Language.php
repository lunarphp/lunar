<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Database\Factories\LanguageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends BaseModel
{
    use HasFactory;
    use HasDefaultRecord;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\LanguageFactory
     */
    protected static function newFactory(): LanguageFactory
    {
        return LanguageFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];
}
