<?php

namespace Lunar\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Blog\Database\Factories\ArticleFactory;
use Lunar\Core\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int|null $author_id
 * @property array|null $body
 * @property string|null $featured_image_alt
 * @property Carbon|null $published_at
 */
class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'blog_articles';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The featured image is a single-file collection: a new upload replaces the
     * previous one rather than accumulating.
     */
    public function registerMediaCollections(): void
    {
        $collection = $this->addMediaCollection(config('lunar-blog.media.collection'))->singleFile();

        if (config('lunar-blog.media.disk') !== null) {
            $collection->useDisk(config('lunar-blog.media.disk'));
        }
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('lunar-blog.author_model'), 'author_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'blog_article_category');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_article_tag');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'blog_article_related_product', 'article_id', 'product_id');
    }

    /**
     * @return BelongsToMany<Article, $this>
     */
    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'blog_article_related_article', 'article_id', 'related_article_id');
    }

    /**
     * Storefront-visible articles: a publish timestamp that has passed. Soft-deleted
     * rows are already excluded by the SoftDeletes global scope.
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    protected static function newFactory(): ArticleFactory
    {
        return ArticleFactory::new();
    }

    public function authorName(): ?string
    {
        if ($this->author === null) {
            return null;
        }

        $name = trim("{$this->author->first_name} {$this->author->last_name}");

        return $name !== '' ? $name : null;
    }

    public function featuredImageUrl(): ?string
    {
        return $this->getFirstMediaUrl(config('lunar-blog.media.collection')) ?: null;
    }

    public function bodyExcerpt(int $limit = 160): ?string
    {
        if (! is_array($this->body)) {
            return null;
        }

        $text = trim((string) preg_replace('/\s+/', ' ', $this->collectBodyText($this->body)));

        return $text !== '' ? Str::limit($text, $limit) : null;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function collectBodyText(array $node): string
    {
        $text = ($node['type'] ?? null) === 'text' ? (string) ($node['text'] ?? '') : '';

        foreach (($node['content'] ?? []) as $child) {
            if (is_array($child)) {
                $text .= ' '.$this->collectBodyText($child);
            }
        }

        return $text;
    }
}
