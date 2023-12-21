<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\StructureDiscoverer\Discover;

class ModelManifest implements ModelManifestInterface
{
    /**
     * The collection of models to register to this manifest.
     */
    protected array $models = [];

    /**
     * Bind initial models in container and set explicit model binding.
     */
    public function register(): void
    {
        // Discover models
        $modelClasses = Discover::in(__DIR__.'/../Models')
            ->classes()
            ->extending(BaseModel::class)
            ->get();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = $this->guessContractClass($modelClass);
            $this->models[$interfaceClass] = $modelClass;
            $this->bindModel($interfaceClass, $modelClass);
        }
    }

    /**
     * Register models.
     */
    public function add(string $interfaceClass, string $modelClass): void
    {
        $this->validateClassIsEloquentModel($modelClass);

        $this->models[$interfaceClass] = $modelClass;

        $this->bindModel($interfaceClass, $modelClass);
    }

    /**
     * Replace a model with a different implementation.
     */
    public function replace(string $interfaceClass, string $modelClass): void
    {
        $this->add($interfaceClass, $modelClass);
    }

    /**
     * Gets the registered class for the interface.
     */
    public function get(string $interfaceClass): ?string
    {
        return $this->models[$interfaceClass] ?? null;
    }

    /**
     * Validate class is an eloquent model.
     *
     * @throws \InvalidArgumentException
     */
    private function validateClassIsEloquentModel(string $class): void
    {
        if (! is_subclass_of($class, Model::class)) {
            throw new \InvalidArgumentException(sprintf('Given [%s] is not a subclass of [%s].', $class, Model::class));
        }
    }

    protected function bindModel(string $interfaceClass, string $modelClass): void
    {
        // Bind in container
        app()->bind($interfaceClass, $modelClass);

        // Route model binding
        Route::model($this->bindingName($modelClass), $modelClass);
    }

    protected function bindingName(string $modelClass): string
    {
        $shortName = (new \ReflectionClass($modelClass))->getShortName();

        return Str::camel($shortName);
    }

    public function guessContractClass(string $modelClass)
    {
        $shortName = (new \ReflectionClass($modelClass))->getShortName();

        return 'Lunar\\Models\\Contracts\\'.$shortName;
    }
}
