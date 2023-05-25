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
     * @return void
     */
    public function handle(Model $model)
    {
        $this->model = $model;

        if (! $model->urls->count()) {
            if ($model->attribute_data) {
                $this->createUrl(
                    $model->attr('name')
                );

                return;
            }

            if ($name = $model->name) {
                $this->createUrl($name);
            }
        }
    }

    /**
     * Create default url from an attribute.
     *
     * @param  string  $value
     * @return void
     */
    protected function createUrl($value)
    {
        $slug = Str::slug($value);

        $this->model->urls()->create([
            'default' => true,
            'language_id' => $this->defaultLanguage->id,
            'slug' => $slug,
        ]);
    }
}
