<?php

namespace Lunar\Core\Manifests;

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
            Route::model($this->bindingName($modelClass), $modelClass);

            if (App::isBooted()) {
                Relation::morphMap([
                    $this->getMorphMapKey($modelClass) => $modelClass,
                ]);
            }
        }
    }

    protected function bindingName(string $modelClass): string
    {
        $shortName = (new \ReflectionClass($modelClass))->getShortName();

        return Str::camel($shortName);
    }
}
