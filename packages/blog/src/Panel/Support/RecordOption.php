<?php

namespace Lunar\Blog\Panel\Support;

use Lunar\Blog\Models\Article;
use Lunar\Core\Models\Product;

/**
 * A single option in a related-record picker: the id the form stores plus the
 * label / meta / thumbnail the picker renders. The same shape is returned by the
 * search endpoint and seeded server-side for already-selected records, so a
 * selection renders before any search runs.
 */
class RecordOption
{
    public function __construct(
        public readonly int $id,
        public readonly string $label,
        public readonly ?string $meta = null,
        public readonly ?string $thumbnail = null,
    ) {}

    public static function fromProduct(Product $product): self
    {
        $sku = $product->variants->first()?->sku;
        $name = $product->translateAttribute('name');

        return new self(
            id: $product->id,
            label: is_string($name) && $name !== '' ? $name : (string) ($sku ?? "Product #{$product->id}"),
            meta: $sku,
            thumbnail: $product->thumbnail?->getUrl('small') ?? $product->thumbnail?->getUrl(),
        );
    }

    public static function fromArticle(Article $article): self
    {
        return new self(
            id: $article->id,
            label: $article->title,
            meta: $article->published_at ? 'Published' : 'Draft',
        );
    }

    /**
     * @return array{id: int, label: string, meta: string|null, thumbnail: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'meta' => $this->meta,
            'thumbnail' => $this->thumbnail,
        ];
    }
}
