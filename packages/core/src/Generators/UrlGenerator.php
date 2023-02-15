<?php

namespace Lunar\Generators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Models\Language;

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
     * @var \Lunar\Models\Language
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
    public function handle(Model $model, $attribute = 'name')
    {
        $this->model = $model;

        if (! $model->urls->count()) {
            if ($model->attribute_data) {
                return $this->createFromAttribute($attribute);
            } elseif ($model->{$attribute}) {
                return $this->generateSlug($model->{$attribute});
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
        $this->generateSlug($this->model->translateAttribute($attribute));
    }

    protected function generateSlug($value)
    {
        $slug = Str::slug($value);

        $this->model->urls()->create([
            'default' => true,
            'language_id' => $this->defaultLanguage->id,
            'slug' => $slug,
        ]);
    }
}
