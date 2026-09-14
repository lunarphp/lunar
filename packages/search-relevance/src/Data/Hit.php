<?php

namespace Lunar\SearchRelevance\Data;

/**
 * One candidate in the ranking window. Cached as arrays, never as objects,
 * so a window written by the CLI can be read under FPM.
 */
final class Hit
{
    public ?float $boost = null;

    public function __construct(
        public readonly int $productId,
        public readonly int $originalPosition,
        public readonly float $score,
        public readonly array $document,
        public readonly string $source = 'organic',
    ) {}

    /** @return array{product_id: int, original_position: int, score: float, document: array, source: string, boost: ?float} */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'original_position' => $this->originalPosition,
            'score' => $this->score,
            'document' => $this->document,
            'source' => $this->source,
            'boost' => $this->boost,
        ];
    }

    public static function fromArray(array $data): self
    {
        $hit = new self(
            (int) $data['product_id'],
            (int) $data['original_position'],
            (float) $data['score'],
            $data['document'],
            $data['source'] ?? 'organic',
        );
        $hit->boost = $data['boost'] ?? null;

        return $hit;
    }

    public function withPosition(int $position): self
    {
        $hit = new self($this->productId, $position, $this->score, $this->document, $this->source);
        $hit->boost = $this->boost;

        return $hit;
    }
}
