<?php

namespace Lunar\SearchRelevance;

use Illuminate\Contracts\Config\Repository;

/**
 * Identifies the normaliser and retrieval setup a learned score was built
 * under, so a normaliser or engine change relearns instead of applying stale
 * scores. Format: n{normaliser_version}:{scout driver}:{hybrid|keyword}.
 */
class RetrievalVersion
{
    public function __construct(protected Repository $config) {}

    public function current(string $modelType): string
    {
        $driver = $this->driver($modelType);

        return implode(':', [
            'n'.$this->config->get('lunar.search_relevance.normaliser_version', 1),
            $driver,
            $this->isHybrid($modelType, $driver) ? 'hybrid' : 'keyword',
        ]);
    }

    public function driver(string $modelType): string
    {
        return (string) ($this->config->get('lunar.search.engine_map', [])[$modelType]
            ?? $this->config->get('scout.driver', 'database'));
    }

    protected function isHybrid(string $modelType, string $driver): bool
    {
        if ($driver === 'typesense') {
            $fields = $this->config->get("scout.typesense.model-settings.{$modelType}.collection-schema.fields", []);

            return collect($fields)->contains(fn ($field) => ($field['name'] ?? null) === 'embedding');
        }

        if ($driver === 'meilisearch') {
            return (bool) $this->config->get('lunar.search.meilisearch.embedder');
        }

        return false;
    }
}
