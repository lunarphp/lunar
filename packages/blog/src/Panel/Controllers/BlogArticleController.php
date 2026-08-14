<?php

namespace Lunar\Blog\Panel\Controllers;

use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Blog\Actions\SyncArticleTerms;
use Lunar\Blog\Models\Article;
use Lunar\Blog\Panel\Requests\BlogArticleRequest;
use Lunar\Blog\Panel\Support\RecordOption;
use Lunar\Core\Models\Product;

/**
 * Blog article panel screens: index, create/edit form, and write actions. The
 * tiptap body editor, featured image, categories/tags, and related-record
 * pickers land in later slices. See docs/specs/blog-creation-plugin.md.
 */
class BlogArticleController
{
    public function __construct(
        private readonly SyncArticleTerms $syncArticleTerms,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $articles = Article::query()
            ->when($search !== '', fn ($query) => $query->whereLike('title', "%{$search}%", caseSensitive: false))
            ->when($status === 'published', fn ($query) => $query->whereNotNull('published_at'))
            ->when($status === 'draft', fn ($query) => $query->whereNull('published_at'))
            ->latest('published_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'status' => $article->published_at ? 'published' : 'draft',
                'published' => $article->published_at
                    ?->setTimezone(config('lunar-blog.publish_timezone'))
                    ->format('j M Y, H:i'),
                '_actions' => [
                    'edit' => route('panel.blog.articles.edit', $article),
                    'delete' => route('panel.blog.articles.destroy', $article),
                ],
            ]);

        return Inertia::render('blog::Articles/Index', [
            'articles' => $articles,
            'filters' => ['q' => $search, 'status' => $status],
            'create_url' => route('panel.blog.articles.create'),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function edit(Article $article): Response
    {
        return $this->form($article);
    }

    public function store(BlogArticleRequest $request): RedirectResponse
    {
        $article = Article::create($request->articleData());
        $this->syncArticleTerms->execute($article, $request->categoryNames(), $request->tagNames());
        $article->relatedProducts()->sync($request->relatedProductIds());
        $article->relatedArticles()->sync($request->relatedArticleIds());
        $this->handleFeaturedImage($request, $article);

        return redirect()
            ->route('panel.blog.articles.edit', $article)
            ->with('success', 'Article created.');
    }

    public function update(BlogArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($request->articleData());
        $this->syncArticleTerms->execute($article, $request->categoryNames(), $request->tagNames());
        $article->relatedProducts()->sync($request->relatedProductIds());
        $article->relatedArticles()->sync($request->relatedArticleIds());
        $this->handleFeaturedImage($request, $article);

        return back()->with('success', 'Article saved.');
    }

    /**
     * Attach an uploaded featured image (replacing any existing one, since the
     * collection is single-file), or clear it when the form asked to remove it.
     */
    private function handleFeaturedImage(BlogArticleRequest $request, Article $article): void
    {
        if ($request->hasFile('featured_image')) {
            $article->addMediaFromRequest('featured_image')->toMediaCollection(config('lunar-blog.media.collection'));
        } elseif ($request->boolean('remove_featured_image')) {
            $article->clearMediaCollection(config('lunar-blog.media.collection'));
        }
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('panel.blog.articles.index')
            ->with('success', 'Article deleted.');
    }

    /**
     * Shared create/edit screen. `create` passes a null article; `edit` seeds
     * the form with the record, its publish time rendered in the trading
     * timezone for the `datetime-local` input.
     */
    private function form(?Article $article): Response
    {
        $article?->loadMissing([
            'categories', 'tags', 'relatedArticles',
            'relatedProducts.variants', 'relatedProducts.thumbnail',
        ]);

        $authorModel = config('lunar-blog.author_model');

        return Inertia::render('blog::Articles/Edit', [
            'article' => $article ? [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'body' => $article->body,
                'featured_image' => $article->getFirstMediaUrl(config('lunar-blog.media.collection')) ?: null,
                'featured_image_alt' => $article->featured_image_alt,
                'author_id' => $article->author_id,
                'seo_title' => $article->seo_title,
                'seo_description' => $article->seo_description,
                'published_at' => $this->tradingTime($article->published_at),
                'categories' => $article->categories->pluck('name'),
                'tags' => $article->tags->pluck('name'),
                'related_products' => $article->relatedProducts
                    ->map(fn (Product $product): array => RecordOption::fromProduct($product)->toArray()),
                'related_articles' => $article->relatedArticles
                    ->map(fn (Article $related): array => RecordOption::fromArticle($related)->toArray()),
            ] : null,
            'authors' => $authorModel::query()
                ->orderBy('first_name')
                ->get()
                ->map(fn ($staff): array => [
                    'id' => $staff->id,
                    'name' => trim("{$staff->first_name} {$staff->last_name}") ?: $staff->email,
                ]),
            'store_url' => route('panel.blog.articles.store'),
            'update_url' => $article ? route('panel.blog.articles.update', $article) : null,
            'search_url' => route('panel.blog.articles.search'),
        ]);
    }

    /**
     * A stored UTC timestamp rendered in the trading timezone as the
     * `Y-m-d\TH:i` value a `datetime-local` input expects.
     */
    private function tradingTime(?CarbonInterface $value): ?string
    {
        return $value
            ?->setTimezone(config('lunar-blog.publish_timezone'))
            ->format('Y-m-d\TH:i');
    }
}
