<?php

namespace Lunar\Panel;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lunar\Panel\Actions\PageActionResolver;
use Lunar\Panel\Contracts\DiscountTypeForm;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Dashboard\WidgetRegistry;
use Lunar\Panel\Models\EditDraft;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Search\SearchCommandResolver;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Search\SearchSourceResolver;
use Lunar\Panel\Sections\ProvidesNavigation;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\SectionExtension;
use Lunar\Panel\Sections\SectionRegistry;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Panel\Tables\Resolvers\TableExtensionResolver;

class PanelManager
{
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

    /** @var array<string, string[]> */
    protected array $pageActions = [];

    /** @var array<int, class-string<SearchSource>> */
    protected array $searchSources = [];

    /** @var array<int, class-string<SearchCommand>> */
    protected array $searchCommands = [];

    /** @var array<class-string<Model>, DraftableResource> */
    protected array $draftables = [];

    /** @var array<class-string, class-string<DiscountTypeForm>> */
    protected array $discountTypeForms = [];

    /** @var Closure[] */
    protected array $routeRegistrars = [];

    /** @var array<int, string> */
    protected array $langNamespaces = [];

    protected bool $sectionsProcessed = false;

    protected SectionRegistry $sectionRegistry;

    protected NavigationRegistry $navigationRegistry;

    protected NavigationRegistry $settingsNavigationRegistry;

    protected SlotRegistry $slotRegistry;

    protected WidgetRegistry $widgetRegistry;

    public function __construct()
    {
        $this->sectionRegistry = new SectionRegistry;
        $this->navigationRegistry = new NavigationRegistry;
        $this->settingsNavigationRegistry = new NavigationRegistry;
        $this->slotRegistry = new SlotRegistry;
        $this->widgetRegistry = new WidgetRegistry;
    }

    public function section(Section $section): static
    {
        $this->sectionRegistry->register($section);
        $this->warnIfRegisteredLate($section::class);

        return $this;
    }

    public function extendSection(SectionExtension $extension): static
    {
        $this->sectionRegistry->registerExtension($extension);
        $this->warnIfRegisteredLate($extension::class);

        return $this;
    }

    /**
     * Sections register in provider boot and are processed once the app has
     * booted; a registration arriving after that would otherwise be silently
     * ignored, so make it diagnosable.
     */
    private function warnIfRegisteredLate(string $class): void
    {
        if ($this->sectionsProcessed) {
            Log::warning("Lunar Panel: [{$class}] was registered after sections were processed and will be ignored; register it in a service provider's boot method.");
        }
    }

    public function processSections(): void
    {
        $this->sectionsProcessed = true;

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

        foreach ($entity->tableExtensions() as $tableId => $extensionClasses) {
            foreach ((array) $extensionClasses as $extensionClass) {
                $this->extendTable($tableId, $extensionClass);
            }
        }

        foreach ($entity->pageActions() as $pageId => $actionClasses) {
            foreach ((array) $actionClasses as $actionClass) {
                $this->addPageAction($pageId, $actionClass);
            }
        }

        foreach ($entity->draftables() as $definitionClass) {
            $this->draftable($definitionClass);
        }

        foreach ($entity->discountTypeForms() as $discountType => $formClass) {
            $this->discountTypeForm($discountType, $formClass);
        }

        foreach ($entity->widgets() as $widgetClass) {
            $this->widget($widgetClass);
        }

        foreach ($entity->searchSources() as $sourceClass) {
            $this->searchSource($sourceClass);
        }

        foreach ($entity->searchCommands() as $commandClass) {
            $this->searchCommand($commandClass);
        }

        if ($viteConfig = $entity->vite()) {
            $this->vite($this->viteKeyFor($sectionKey, $entity), $viteConfig);
        }

        if ($namespaces = $entity->langNamespaces()) {
            $this->translations(...$namespaces);
        }
    }

    /**
     * A unique Vite module key per entity: the section's own key, or a derived
     * key for extensions so an extension's config never clobbers its target
     * section's (or a sibling extension's). Also the public asset path segment
     * (vendor/lunar-panel/{key}), so it must be filesystem-safe.
     */
    private function viteKeyFor(string $sectionKey, ProvidesNavigation $entity): string
    {
        if ($entity instanceof SectionExtension) {
            return $sectionKey.'-'.Str::kebab(class_basename($entity));
        }

        return $sectionKey;
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

    /** @param class-string<Dashboard\Widget> $widgetClass */
    public function widget(string $widgetClass): static
    {
        $this->widgetRegistry->add($widgetClass);

        return $this;
    }

    public function widgets(): WidgetRegistry
    {
        return $this->widgetRegistry;
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
        return new TableExtensionResolver($this->getTableExtensions($tableId), $this->user());
    }

    /**
     * @param  class-string  $discountType
     * @param  class-string<DiscountTypeForm>  $formClass
     */
    public function discountTypeForm(string $discountType, string $formClass): static
    {
        $this->discountTypeForms[$discountType] = $formClass;

        return $this;
    }

    /** @return array<class-string, class-string<DiscountTypeForm>> */
    public function discountTypeForms(): array
    {
        return $this->discountTypeForms;
    }

    /** @param class-string $actionClass */
    public function addPageAction(string $pageId, string $actionClass): static
    {
        $this->pageActions[$pageId][] = $actionClass;

        return $this;
    }

    /** @return string[] */
    public function getPageActions(string $pageId): array
    {
        return $this->pageActions[$pageId] ?? [];
    }

    public function resolvePageActions(string $pageId): PageActionResolver
    {
        return new PageActionResolver($this->getPageActions($pageId), $this->user());
    }

    /** @param class-string<SearchSource> $sourceClass */
    public function searchSource(string $sourceClass): static
    {
        $this->searchSources[] = $sourceClass;

        return $this;
    }

    /** @return array<int, class-string<SearchSource>> */
    public function getSearchSources(): array
    {
        return $this->searchSources;
    }

    public function resolveSearchSources(): SearchSourceResolver
    {
        return new SearchSourceResolver($this->getSearchSources(), $this->user());
    }

    /** @param class-string<SearchCommand> $commandClass */
    public function searchCommand(string $commandClass): static
    {
        $this->searchCommands[] = $commandClass;

        return $this;
    }

    /** @return array<int, class-string<SearchCommand>> */
    public function getSearchCommands(): array
    {
        return $this->searchCommands;
    }

    public function resolveSearchCommands(): SearchCommandResolver
    {
        return new SearchCommandResolver($this->getSearchCommands(), $this->user());
    }

    /**
     * Register a draftable-resource definition, keyed by its model class.
     * Registration also removes a record's drafts when the record is deleted,
     * so drafts never point at gone records.
     *
     * @param  class-string<DraftableResource>  $definitionClass
     */
    public function draftable(string $definitionClass): static
    {
        /** @var DraftableResource $definition */
        $definition = app($definitionClass);

        $model = $definition->model();

        if (isset($this->draftables[$model])) {
            Log::warning("Lunar Panel: draftable resource for [{$model}] is already registered and will be overwritten.");
        } else {
            $model::deleted(function (Model $record): void {
                EditDraft::query()
                    ->where('draftable_type', $record->getMorphClass())
                    ->where('draftable_id', $record->getKey())
                    ->delete();
            });
        }

        $this->draftables[$model] = $definition;

        return $this;
    }

    public function draftableFor(Model $model): ?DraftableResource
    {
        return $this->draftables[$model::class] ?? null;
    }

    /**
     * Expose translator namespaces to the panel frontend: every lang group
     * under each namespace is served by the translations endpoint as
     * `{namespace}::{group}` message keys. Registered automatically from
     * `Section::langNamespaces()`, or directly for non-section callers.
     */
    public function translations(string ...$namespaces): static
    {
        $this->langNamespaces = array_values(array_unique([...$this->langNamespaces, ...$namespaces]));

        return $this;
    }

    /** @return array<int, string> */
    public function translationNamespaces(): array
    {
        return $this->langNamespaces;
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

    /**
     * Register a Vite configuration for a panel module. The optional
     * __buildSourcePath points at the module's compiled build directory on
     * disk so `lunar:panel:link` can symlink it into public/.
     *
     * @param  array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|string[]  $config
     */
    public function vite(string $name, array|string $config): static
    {
        if (isset($this->vites[$name])) {
            Log::warning("Lunar Panel: Vite module [{$name}] is already registered and will be overwritten.");
        }

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

    public function path(): string
    {
        return config('lunar.panel.path', 'panel');
    }

    public function guard(): string
    {
        return config('lunar.panel.guard') ?: config('lunar.staff.guard', 'staff');
    }

    /**
     * The authenticated panel user, always resolved from the panel guard so
     * visibility checks never depend on the request's default guard.
     */
    public function user(): ?Authenticatable
    {
        return auth($this->guard())->user();
    }

    public function name(): string
    {
        return config('lunar.panel.name', 'Lunar');
    }

    /**
     * The locales the panel UI ships translations for — the locale directories
     * under the panel package's resources/lang. Drives the locale switcher and
     * validates the preferred-locale update.
     *
     * @return array<int, string>
     */
    public function availableLocales(): array
    {
        $langPath = dirname(__DIR__).'/resources/lang';

        $locales = array_map(
            'basename',
            glob("{$langPath}/*", GLOB_ONLYDIR) ?: [],
        );

        sort($locales);

        return $locales ?: ['en'];
    }
}
