<?php

namespace Lunar\SearchRelevance\Signals;

use Illuminate\Contracts\Container\Container;
use Lunar\SearchRelevance\Contracts\Signal;
use Lunar\SearchRelevance\DataObjects\RankingContext;

class SignalCombiner
{
    /** @param array<class-string<Signal>, float> $weights */
    public function __construct(
        protected array $weights,
        protected Container $container,
    ) {}

    /**
     * Weighted sum of every configured signal, clamped to 0..1.
     *
     * @param  array<int, int>  $productIds
     * @return array<int, float>
     */
    public function combine(RankingContext $context, array $productIds): array
    {
        $combined = [];

        foreach ($this->weights as $class => $weight) {
            /** @var Signal $signal */
            $signal = $this->container->make($class);

            foreach ($signal->scores($context, $productIds) as $productId => $score) {
                $combined[$productId] = ($combined[$productId] ?? 0.0) + $score * $weight;
            }
        }

        return array_map(fn (float $score) => min(1.0, max(0.0, $score)), $combined);
    }
}
