<?php

namespace Lunar\Panel;

use Closure;
use Illuminate\Support\Facades\Log;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\ProvidesNavigation;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\SectionExtension;
use Lunar\Panel\Sections\SectionRegistry;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Panel\Tables\Resolvers\TableExtensionResolver;

class PanelManager
{
    /** @var array<string, string> */
    protected array $scripts = [];

    /** @var array<string, string> */
    protected array $styles = [];

    /** @var array<string, string> */
    protected array $assets = [];

    /**
     * Vite configurations keyed by module name.
     *
     * @var array<string, array{input: string|string[], hotFile: string|null, buildDirectory: string}>
     */
    protected array $vites = [];

    /** @var array<string, string> */
    protected array $viteBuildPaths = [];

    /** @var array<string, string[]> */
    protected array $tableExtensions = [];

    /** @var Closure[] */
    protected array $routeRegistrars = [];

    protected SectionRegistry $sectionRegistry;

    protected NavigationRegistry $navigationRegistry;

    protected NavigationRegistry $settingsNavigationRegistry;

    protected SlotRegistry $slotRegistry;

    public function __construct()
    {
        $this->sectionRegistry = new SectionRegistry;
        $this->navigationRegistry = new NavigationRegistry;
        $this->settingsNavigationRegistry = new NavigationRegistry;
        $this->slotRegistry = new SlotRegistry;
    }

    public function section(Section $section): static
    {
        $this->sectionRegistry->register($section);

        return $this;
    }

    public function extendSection(SectionExtension $extension): static
    {
        $this->sectionRegistry->registerExtension($extension);

        return $this;
    }

    public function processSections(): void
    {
        $sections = $this->sectionRegistry->all();
        $allExtensions = $this->sectionRegistry->extensions();

        foreach ($sections as $section) {
            $this->processEntity($section->key(), $section);

            foreach ($allExtensions[$section->key()] ?? [] as $extension) {
                $this->processEntity($section->key(), $extension);
            }
        }

        foreach (array_keys($allExtensions) as $key) {
            if (! $this->sectionRegistry->has($key)) {
                Log::warning("Lunar Panel: SectionExtension targets unknown section key [{$key}].");
            }
        }
    }

    private function processEntity(string $sectionKey, ProvidesNavigation $entity): void
    {
        $this->navigationRegistry->beginSection($sectionKey);
        $entity->navigation($this->navigationRegistry);
        $this->navigationRegistry->endSection();

        $entity->settingsNavigation($this->settingsNavigationRegistry);

        $entity->slots($this->slotRegistry);

        if ($routes = $entity->routes()) {
            $this->registerRoutes($routes);
        }

        foreach ($entity->tableExtensions() as $tableId => $extensionClass) {
            $this->extendTable($tableId, $extensionClass);
        }

        if ($viteConfig = $entity->vite()) {
            $this->vite($sectionKey, $viteConfig);
        }
    }

    public function navigation(): NavigationRegistry
    {
        return $this->navigationRegistry;
    }

    public function settingsNavigation(): NavigationRegistry
    {
        return $this->settingsNavigationRegistry;
    }

    public function slots(): SlotRegistry
    {
        return $this->slotRegistry;
    }

    public function extendTable(string $tableId, string $extensionClass): static
    {
        $this->tableExtensions[$tableId][] = $extensionClass;

        return $this;
    }

    /** @return string[] */
    public function getTableExtensions(string $tableId): array
    {
        return $this->tableExtensions[$tableId] ?? [];
    }

    public function resolveExtensions(string $tableId): TableExtensionResolver
    {
        return new TableExtensionResolver($this->getTableExtensions($tableId));
    }

    public function registerRoutes(Closure $callback): static
    {
        $this->routeRegistrars[] = $callback;

        return $this;
    }

    /** @return Closure[] */
    public function getRouteRegistrars(): array
    {
        return $this->routeRegistrars;
    }

    public function registerAssets(string $key, string $buildPath): static
    {
        $this->assets[$key] = $buildPath;
        $this->scripts[$key] = asset("vendor/lunar-panel/{$key}/app.js");

        return $this;
    }

    /** @return array<string, string> */
    public function assets(): array
    {
        return $this->assets;
    }

    /**
     * Register a Vite configuration for a panel module.
     *
     * @param  array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|string[]  $config
     */
    public function vite(string $name, array|string $config): static
    {
        if (is_string($config) || (is_array($config) && array_is_list($config))) {
            $config = ['input' => $config];
        }

        if (isset($config['__buildSourcePath'])) {
            $this->viteBuildPaths[$name] = $config['__buildSourcePath'];
            unset($config['__buildSourcePath']);
        }

        $this->vites[$name] = array_merge([
            'hotFile' => null,
            'buildDirectory' => 'build',
        ], $config);

        return $this;
    }

    /**
     * @return array<string, array{input: string|string[], hotFile: string|null, buildDirectory: string}>
     */
    public function registeredVites(): array
    {
        return $this->vites;
    }

    /** @return array<string, string> */
    public function viteBuildPaths(): array
    {
        return $this->viteBuildPaths;
    }

    public function registerScript(string $name, string $path): static
    {
        $this->scripts[$name] = $path;

        return $this;
    }

    public function registerStyle(string $name, string $path): static
    {
        $this->styles[$name] = $path;

        return $this;
    }

    /** @return array<string, string> */
    public function scripts(): array
    {
        return $this->scripts;
    }

    /** @return array<string, string> */
    public function styles(): array
    {
        return $this->styles;
    }

    public function path(): string
    {
        return config('lunar.panel.path', 'panel');
    }

    public function guard(): string
    {
        return config('lunar.panel.guard') ?: config('lunar.staff.guard', 'staff');
    }

    public function name(): string
    {
        return config('lunar.panel.name', 'Lunar');
    }
}
