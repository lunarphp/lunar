<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Models\Url;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait HasUrls
{
    public array $urls = [];

    public function mountHasUrls()
    {
        $this->urls = $this->getHasUrlsModel()->urls->toArray();
    }

    /**
     * Computed property for existing tags.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function getHasUrlsModel();

    public function addUrl()
    {
        $this->urls[] = [
            'slug'        => null,
            'default'     => !collect($this->urls)->count(),
            'language_id' => $this->defaultLanguage->id,
        ];
    }

    public function removeUrl($index)
    {
        unset($this->urls[$index]);
    }

    /**
     * Listener for when the slug is updated.
     *
     * @param string $value
     *
     * @return void
     */
    public function updatedUrls($value, $key)
    {
        Arr::set($this->urls, $key, Str::slug($value));
    }

    public function saveUrls()
    {
        $rules = [];
        foreach ($this->urls as $index => $url) {
            $rules["urls.{$index}.slug"] = [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($url) {
                    $result = collect($this->urls)->filter(function ($existing) use ($value, $url) {
                        return $existing['slug'] == $value &&
                        $existing['language_id'] == $url['language_id'];
                    })->count();

                    if ($result > 1) {
                        $fail(
                            __('adminhub::validation.url_slug_unique')
                        );
                    }
                },
                Rule::unique(Url::class, 'slug')->where(function ($query) use ($url) {
                    $query->where('slug', '=', $url['slug'])
                        ->where('language_id', '=', $url['language_id']);

                    if ($url['id'] ?? false) {
                        $query->where('id', '!=', $url['id']);
                    }
                }),
            ];

            $rules["urls.{$index}.default"] = [
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) use ($url) {
                    $result = collect($this->urls)->filter(function ($existing) use ($value, $url) {
                        return $existing['default'] == $value &&
                        $existing['language_id'] == $url['language_id'];
                    })->count();

                    if ($value && $result > 1) {
                        $fail(
                            __('adminhub::validation.url_default_unique')
                        );
                    }
                },
            ];
        }

        if (!empty($rules)) {
            $this->validate($rules, [
                'urls.*.slug.unique' => __('adminhub::validation.url_slug_unique'),
            ]);
        }

        $model = $this->getHasUrlsModel();

        DB::transaction(function () use ($model) {
            // Delete any that have been removed.
            $model->urls->reject(function ($url) {
                return collect($this->urls)->pluck('id')->contains($url->id);
            })->each(fn ($url) => $url->delete());

            foreach ($this->urls as $index => $url) {
                $urlModel = ($url['id'] ?? false) ? Url::find($url['id']) : new Url();
                $urlModel->fill($url);
                $urlModel->element_type = get_class($model);
                $urlModel->element_id = $model->id;
                $urlModel->save();
                $this->urls[$index]['id'] = $urlModel->id;
            }
        });
    }
}
