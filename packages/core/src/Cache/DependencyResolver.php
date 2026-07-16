<?php

namespace Lunar\Core\Cache;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\CacheDependencies;
use Lunar\Core\Contracts\DependencyResolver as DependencyResolverContract;

class DependencyResolver implements DependencyResolverContract
{
    /**
     * @param  bool  $strict  Throw on an unknown relation path (dev/CI) rather
     *                        than skip + log (production).
     */
    public function __construct(
        protected CacheDependencies $graphs,
        protected bool $strict,
    ) {}

    public function for(Model $root, ?string $graph = null): array
    {
        $tags = $this->tagsFor($root);

        $definition = $this->graphs->get($graph ?? $root->getMorphClass());

        if ($definition instanceof Closure) {
            $tags = array_merge($tags, $this->normalise($definition($root)));
        } elseif (is_array($definition)) {
            foreach ($definition as $path) {
                $tags = array_merge($tags, $this->resolvePath($root, $path));
            }
        }

        return array_values(array_unique($tags));
    }

    /**
     * Walk a dot-notation relation path from the root and collect the tags of
     * the cache-participating leaf models.
     *
     * @return array<int, string>
     */
    protected function resolvePath(Model $root, string $path): array
    {
        try {
            $root->loadMissing($path);
        } catch (RelationNotFoundException $e) {
            if ($this->strict) {
                throw $e;
            }

            logger()->warning(
                "Lunar cache dependency graph: unknown relation path [{$path}] on [".$root::class.'] — skipped.'
            );

            return [];
        }

        $models = collect([$root]);

        foreach (explode('.', $path) as $segment) {
            $models = $models->flatMap(function (Model $model) use ($segment) {
                $related = $model->getRelationValue($segment);

                return $related instanceof Collection
                    ? $related
                    : collect(array_filter([$related]));
            });
        }

        return $models->flatMap(fn ($model) => $this->tagsFor($model))->all();
    }

    /**
     * @return array<int, string>
     */
    protected function tagsFor(mixed $model): array
    {
        return $model instanceof Model && method_exists($model, 'cacheTags')
            ? $model->cacheTags()
            : [];
    }

    /**
     * Normalise a closure's return (models and/or tag strings) to tags.
     *
     * @return array<int, string>
     */
    protected function normalise(iterable $items): array
    {
        $tags = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $tags[] = $item;
            } else {
                $tags = array_merge($tags, $this->tagsFor($item));
            }
        }

        return $tags;
    }
}
