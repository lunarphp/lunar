<?php

namespace Lunar\Core\Manifests;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
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
     * Discover the core models and register their route + morph bindings.
     */
    public function register(): void
    {
        $this->registerModels(
            Discover::in(__DIR__.'/../Models')->classes()->extending(Base::class)->get()
        );
    }

    /**
     * Register the models discovered in a directory.
     */
    public function addDirectory(string $dir): void
    {
        try {
            $this->registerModels(
                Discover::in($dir)->classes()->extending(Base::class)->get()
            );
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
            Discover::in(__DIR__.'/../Models')->classes()->extending(Base::class)->get()
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
