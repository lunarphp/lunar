<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

trait CanRegisterLivewireComponentDirectories
{
    /**
     * Register a directory of livewire components.
     *
     * @param  string  $directory
     * @param  string  $namespace
     * @param  string  $aliasPrefix
     * @return void
     */
    protected function registerLivewireComponentDirectory(string $directory, string $namespace, string $aliasPrefix = ''): void
    {
        $filesystem = app(Filesystem::class);
        if (! $filesystem->isDirectory($directory)) {
            return;
        }

        collect($filesystem->allFiles($directory))
            ->map(function (SplFileInfo $file) use ($namespace): string {
                return (string) Str::of($namespace)
                    ->append('\\', $file->getRelativePathname())
                    ->replace(['/', '.php'], ['\\', '']);
            })
            ->filter(function (string $class): bool {
                return is_subclass_of($class, Component::class) && (! (new ReflectionClass($class))->isAbstract());
            })
            ->each(callback: function (string $class) use ($namespace, $aliasPrefix): void {
                $classNamespace = (new ReflectionClass($class))->getNamespaceName();
                $namespace = (string) Str::of($classNamespace)->replace($namespace, '')->after('\\');
                $namespace = collect(explode('.', str_replace(['/', '\\'], '.', $namespace)))
                    ->map([Str::class, 'kebab'])
                    ->implode('.');

                Livewire::component($this->findAliasFromComponent($aliasPrefix, $namespace, $class), $class);
            });
    }

    /**
     * Find the alias for a component.
     *
     * @param  string  $aliasPrefix
     * @param  string  $namespace
     * @param  string  $class
     * @return string
     */
    protected function findAliasFromComponent(string $aliasPrefix, string $namespace, string $class): string
    {
        $alias = Str::of($class)
            ->after($namespace.'\\')
            ->replace(['/', '\\'], '.')
            ->prepend($aliasPrefix)
            ->replace($aliasPrefix, '')
            ->explode('.')
            ->map([Str::class, 'kebab'])
            ->implode('.');

        $aliasBasename = Str::of($alias)->afterLast('.');
        if (Str::endsWith($aliasBasename, ['index', 'show', 'edit', 'create'])) {
            $aliasBasename = Str::of($aliasBasename)->afterLast('-');
        }

        $overrideAlias = (new ReflectionClass($class))->getStaticProperties()['overrideComponentAlias'] ?? null;
        if ($overrideAlias) {
            $aliasBasename = $overrideAlias;
        }

        return $aliasPrefix.'.'.$namespace.'.'.$aliasBasename;
    }
}
