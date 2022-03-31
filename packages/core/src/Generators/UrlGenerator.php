<?php

namespace GetCandy\Generators;

use GetCandy\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UrlGenerator
{
    /**
     * The instance of the model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The default language.
     *
     * @var \GetCandy\Models\Language
     */
    protected Language $defaultLanguage;

    /**
     * Construct the class.
     */
    public function __construct()
    {
        $this->defaultLanguage = Language::getDefault();
    }

    /**
     * Handle the URL generation.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function handle(Model $model)
    {
        $this->model = $model;

        if (! $model->urls->count()) {
            if ($model->attribute_data) {
                return $this->createFromAttribute('name');
            }
        }
    }

    /**
     * Create default url from an attribute.
     *
     * @param  string  $attribute
     * @return void
     */
    protected function createFromAttribute($attribute)
    {
        $slug = Str::slug(
            $this->model->translateAttribute($attribute)
        );

        $this->model->urls()->create([
            'default' => true,
            'language_id' => $this->defaultLanguage->id,
            'slug' => $slug,
        ]);
    }
}
