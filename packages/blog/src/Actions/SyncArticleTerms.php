<?php

namespace Lunar\Blog\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Blog\Models\Article;
use Lunar\Blog\Models\Category;
use Lunar\Blog\Models\Tag;

/**
 * Resolve category and tag names entered on the article form to term records,
 * creating any that do not yet exist (matched by slug), and sync them onto the
 * article. This is what makes the form's "assign existing or create new"
 * behaviour a single input: a name that matches an existing slug reuses it,
 * otherwise a new term is created.
 */
class SyncArticleTerms
{
    /**
     * @param  array<int, string>  $categoryNames
     * @param  array<int, string>  $tagNames
     */
    public function execute(Article $article, array $categoryNames, array $tagNames): void
    {
        $article->categories()->sync($this->resolveIds(Category::class, $categoryNames));
        $article->tags()->sync($this->resolveIds(Tag::class, $tagNames));
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<int, string|null>  $names
     * @return array<int, int>
     */
    private function resolveIds(string $model, array $names): array
    {
        return collect($names)
            ->map(fn (?string $name): string => trim((string) $name))
            ->filter()
            ->unique(fn (string $name): string => Str::slug($name))
            ->map(fn (string $name): int => $model::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            )->id)
            ->values()
            ->all();
    }
}
