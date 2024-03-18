<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Lunar\Admin\Models\Staff;
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

    public function guessContractClass(string $modelClass): string
    {
        $shortName = (new \ReflectionClass($modelClass))->getShortName();

        return 'Lunar\\Models\\Contracts\\'.$shortName;
    }

    public function getResolvedTableName(BaseModel $model): string
    {
        $class = (new \ReflectionClass($model));

        $contract = array_flip($this->models)[$class->getName()] ?? null;

        $tableClass = $model;

        if ($contract) {
            $tableClass = collect(Discover::in(__DIR__.'/../Models')->classes()->implementing(
                $contract
            )->get())->first();
        }

        return Str::snake(
            Str::pluralStudly(
                class_basename($tableClass)
            )
        );
    }

    public function morphMap()
    {
        $modelClasses = collect(
            Discover::in(__DIR__.'/../Models')
                ->classes()
                ->extending(BaseModel::class)
                ->get()
        )->mapWithKeys(
            fn ($class) => [
                \Illuminate\Support\Str::snake(class_basename($class)) => $class::modelClass(),
            ]
        )->merge([
            'staff' => Staff::class,
        ]);

        Relation::enforceMorphMap(
            $modelClasses->toArray()
        );
    }
}
