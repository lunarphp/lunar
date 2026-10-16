<?php

namespace Lunar\Core\Manifests;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\ModelManifest as ModelManifestContract;
use Lunar\Core\Models\Base;
use Spatie\StructureDiscoverer\Discover;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class ModelManifest implements ModelManifestContract
{
    /**
     * Model classes already discovered, keyed by directory.
     *
     * Discovery parses every file in the directory, and register() and
     * morphMap() both need the core models on every boot. Remembering the
     * result keeps that to one scan per directory for the life of the
     * manifest, which is bound as a singleton.
     *
     * @var array<string, array<class-string>>
     */
    protected array $discovered = [];

    /**
     * The contents of the cache file, loaded on first use.
     *
     * @var array<string, array<class-string>>|null
     */
    protected ?array $cached = null;

    /**
     * Discover the core models and register their route + morph bindings.
     */
    public function register(): void
    {
        $this->registerModels($this->discover($this->coreModelsPath()));
    }

    /**
     * Register the models discovered in a directory.
     */
    public function addDirectory(string $dir): void
    {
        try {
            $this->registerModels($this->discover($dir));
        } catch (DirectoryNotFoundException $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Register the morph map for the core models.
     */
    public function morphMap(): void
    {
        $morphMap = collect(
            $this->discover($this->coreModelsPath())
        )->mapWithKeys(
            fn (string $class) => [$this->getMorphMapKey($class) => $class]
        );

        Relation::morphMap($morphMap->toArray());
    }

    public function getMorphMapKey(string $className): string
    {
        $prefix = config('lunar.database.morph_prefix', null);
        $key = Str::snake(class_basename($className));

        return "{$prefix}{$key}";
    }

    /**
     * Rescan every directory this manifest has discovered and write the
     * result to the cache file, which later processes read instead of
     * scanning. A stale file is ignored while building.
     *
     * @return array<string, array<class-string>>
     */
    public function cache(): array
    {
        $this->discover($this->coreModelsPath());

        $models = [];

        foreach (array_keys($this->discovered) as $dir) {
            $models[$dir] = $this->scan($dir);
        }

        File::replace($this->cachePath(), '<?php return '.var_export($models, true).';'.PHP_EOL);

        return $this->discovered = $this->cached = $models;
    }

    /**
     * Delete the cache file, so the next process scans again.
     */
    public function clearCache(): void
    {
        File::delete($this->cachePath());

        $this->cached = [];
    }

    public function cachePath(): string
    {
        return App::bootstrapPath('cache/lunar_models.php');
    }

    /**
     * The model classes in a directory: from the cache file when it lists
     * the directory, otherwise scanned, and either way at most once.
     *
     * @return array<class-string>
     */
    protected function discover(string $dir): array
    {
        $dir = realpath($dir) ?: $dir;

        return $this->discovered[$dir] ??= $this->cached()[$dir] ?? $this->scan($dir);
    }

    /**
     * @return array<string, array<class-string>>
     */
    protected function cached(): array
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $path = $this->cachePath();
        $models = is_file($path) ? require $path : [];

        return $this->cached = is_array($models) ? $models : [];
    }

    /**
     * Scan a directory for classes extending the Lunar base model.
     *
     * @return array<class-string>
     */
    protected function scan(string $dir): array
    {
        return Discover::in($dir)->classes()->extending(Base::class)->get();
    }

    protected function coreModelsPath(): string
    {
        return __DIR__.'/../Models';
    }

    /**
     * @param  array<class-string>  $modelClasses
     */
    protected function registerModels(array $modelClasses): void
    {
        foreach ($modelClasses as $modelClass) {
            $this->bindRouteParameter($this->bindingName($modelClass), $modelClass);

            if (App::isBooted()) {
                Relation::morphMap([
                    $this->getMorphMapKey($modelClass) => $modelClass,
                ]);
            }
        }
    }

    /**
     * Bind the route parameter for a model.
     *
     * Not Route::model(): explicit binders run before Laravel's implicit
     * binding pass and resolve every parameter independently, so a nested
     * route wrapped in Route::scopeBindings() would never scope the child
     * through its parent (e.g. a URL under the wrong brand would still
     * resolve). This binder applies the implicit resolver's scoping rules
     * first and only then falls back to a plain lookup.
     */
    protected function bindRouteParameter(string $name, string $modelClass): void
    {
        Route::bind($name, function (mixed $value, $route) use ($name, $modelClass) {
            $field = $route?->bindingFieldFor($name);
            $parent = $route?->parentOfParameter($name);

            // Explicit binders run in URI-segment order, so a scoped parent
            // has already been resolved to a model by the time this runs.
            $shouldScope = $parent instanceof UrlRoutable
                && ! $route->preventsScopedBindings()
                && ($route->enforcesScopedBindings() || $field !== null);

            $resolved = $shouldScope
                ? $parent->resolveChildRouteBinding($name, $value, $field)
                : app($modelClass)->resolveRouteBinding($value, $field);

            return $resolved ?? throw (new ModelNotFoundException)->setModel($modelClass, [$value]);
        });
    }

    protected function bindingName(string $modelClass): string
    {
        $shortName = (new \ReflectionClass($modelClass))->getShortName();

        return Str::camel($shortName);
    }
}
