<?php

namespace Lunar\Blog\Panel\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Lunar\Blog\Models\Article;

class BlogArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware already enforces `can:blog:manage`; nothing further
        // to gate at the request level.
        return true;
    }

    /**
     * Derive a slug from the title when none is supplied, and convert the
     * `datetime-local` publish time (zoneless branch wall-clock) to UTC before
     * validation so date rules and storage both work in one zone.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->filled('slug') && $this->filled('title')) {
            $merge['slug'] = Str::slug((string) $this->input('title'));
        }

        if ($this->filled('published_at')) {
            $merge['published_at'] = CarbonImmutable::parse(
                (string) $this->input('published_at'),
                config('lunar-blog.publish_timezone'),
            )->utc();
        }

        // An article can never be its own related article — drop its own id
        // before validation rather than rejecting the whole request.
        if ($this->has('related_articles') && ($article = $this->routeArticle())) {
            $merge['related_articles'] = collect((array) $this->input('related_articles'))
                ->reject(fn ($id): bool => (int) $id === $article->id)
                ->values()
                ->all();
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $this->uniqueSlug()],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'array'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'remove_featured_image' => ['boolean'],
            'author_id' => ['required', $this->authorExists()],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'categories' => ['array'],
            'categories.*' => ['nullable', 'string', 'max:255'],
            'tags' => ['array'],
            'tags.*' => ['nullable', 'string', 'max:255'],
            'related_products' => ['array'],
            'related_products.*' => ['integer', Rule::exists('lunar_products', 'id')],
            'related_articles' => ['array'],
            'related_articles.*' => ['integer', Rule::exists('blog_articles', 'id')],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function relatedProductIds(): array
    {
        return array_map('intval', array_values($this->validated('related_products', [])));
    }

    /**
     * @return array<int, int>
     */
    public function relatedArticleIds(): array
    {
        return array_map('intval', array_values($this->validated('related_articles', [])));
    }

    /**
     * Category names entered on the form.
     *
     * @return array<int, string>
     */
    public function categoryNames(): array
    {
        return array_values($this->validated('categories', []));
    }

    /**
     * Tag names entered on the form.
     *
     * @return array<int, string>
     */
    public function tagNames(): array
    {
        return array_values($this->validated('tags', []));
    }

    private function uniqueSlug(): Unique
    {
        $rule = Rule::unique('blog_articles', 'slug')->withoutTrashed();

        if ($article = $this->routeArticle()) {
            $rule->ignore($article->id);
        }

        return $rule;
    }

    private function authorExists(): Exists
    {
        /** @var class-string<Model> $authorModel */
        $authorModel = config('lunar-blog.author_model');

        return Rule::exists((new $authorModel)->getTable(), 'id');
    }

    private function routeArticle(): ?Article
    {
        $article = $this->route('article');

        return $article instanceof Article ? $article : null;
    }

    /**
     * Validated attributes with `published_at` as a Carbon instance (or null).
     *
     * @return array<string, mixed>
     */
    public function articleData(): array
    {
        $data = $this->safe()->only([
            'title', 'slug', 'excerpt', 'body', 'featured_image_alt',
            'author_id', 'seo_title', 'seo_description', 'published_at',
        ]);

        // The value merged in prepareForValidation() is already a UTC instant,
        // but casting it to a string loses that timezone (Carbon's __toString
        // omits it), so it must be re-parsed explicitly as UTC rather than
        // left to fall back to app.timezone.
        $data['published_at'] = $this->filled('published_at')
            ? CarbonImmutable::parse((string) $this->input('published_at'), 'UTC')
            : null;

        return $data;
    }
}
