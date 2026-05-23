<?php

namespace Lunar\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

use function Laravel\Prompts\multiselect;

class PublishAdminResourcesCommand extends Command
{
    protected $description = 'Publish Lunar admin resources into your application so you can own them.';

    protected $signature = 'lunar:admin:publish
                            {resources?* : The resource keys to publish (e.g. products orders customers)}
                            {--all : Publish every resource}
                            {--namespace=App\\Filament\\Resources : Target PHP namespace for the published resources}
                            {--path=app/Filament/Resources : Target filesystem path for the published resources}
                            {--force : Overwrite existing files at the target path}';

    /**
     * Source directory for the admin's resources.
     */
    protected string $sourceDir;

    /**
     * Source namespace prefix for the admin's resources.
     */
    protected string $sourceNamespace = 'Lunar\\Admin\\Filament\\Resources';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();

        $this->sourceDir = realpath(__DIR__.'/../../Filament/Resources') ?: '';
    }

    public function handle(): int
    {
        $available = $this->discoverResources();

        if (empty($available)) {
            $this->components->error('No Lunar admin resources were discovered.');

            return self::FAILURE;
        }

        $selectedKeys = $this->resolveSelectedKeys($available);

        if (empty($selectedKeys)) {
            $this->components->warn('No resources selected — nothing to publish.');

            return self::SUCCESS;
        }

        $targetNamespace = trim($this->option('namespace'), '\\');
        $rawPath = $this->option('path');
        $targetPath = str_starts_with($rawPath, '/') ? $rawPath : base_path(trim($rawPath, '/'));

        foreach ($selectedKeys as $key) {
            if (! isset($available[$key])) {
                $this->components->error("Unknown resource key: {$key}. Run with --help to list available keys.");

                continue;
            }

            $this->publishResource($available[$key], $targetNamespace, $targetPath);
        }

        $this->newLine();
        $this->components->info('Done. Register the published resources on your panel, then call:');
        $this->line('  LunarPanel::excludeResources([');
        foreach ($selectedKeys as $key) {
            if (isset($available[$key])) {
                $this->line('      \\'.$available[$key]['class'].'::class,');
            }
        }
        $this->line('  ]);');

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{key: string, class: string, name: string, file: string, dir: ?string}>
     */
    protected function discoverResources(): array
    {
        if (! $this->files->isDirectory($this->sourceDir)) {
            return [];
        }

        $resources = [];

        foreach ($this->files->files($this->sourceDir) as $file) {
            $name = $file->getFilenameWithoutExtension();

            if (! Str::endsWith($name, 'Resource')) {
                continue;
            }

            $stem = Str::beforeLast($name, 'Resource');
            $key = Str::plural(Str::kebab($stem));

            $resources[$key] = [
                'key' => $key,
                'class' => $this->sourceNamespace.'\\'.$name,
                'name' => $name,
                'file' => $file->getPathname(),
                'dir' => $this->files->isDirectory($this->sourceDir.'/'.$name) ? $this->sourceDir.'/'.$name : null,
            ];
        }

        ksort($resources);

        return $resources;
    }

    /**
     * @param  array<string, array{key: string, class: string, name: string, file: string, dir: ?string}>  $available
     * @return array<int, string>
     */
    protected function resolveSelectedKeys(array $available): array
    {
        if ($this->option('all')) {
            return array_keys($available);
        }

        $keys = (array) $this->argument('resources');

        if (! empty($keys)) {
            return $keys;
        }

        return (array) multiselect(
            label: 'Which resources do you want to publish?',
            options: array_combine(array_keys($available), array_map(fn ($r) => $r['name'], $available)),
            scroll: 15,
            hint: 'Use space to select.',
        );
    }

    /**
     * @param  array{key: string, class: string, name: string, file: string, dir: ?string}  $resource
     */
    protected function publishResource(array $resource, string $targetNamespace, string $targetPath): void
    {
        if (! $this->files->isDirectory($targetPath)) {
            $this->files->makeDirectory($targetPath, recursive: true);
        }

        $sourcePrefix = $this->sourceNamespace.'\\'.$resource['name'];
        $targetPrefix = $targetNamespace.'\\'.$resource['name'];

        $rootSourceFile = $resource['file'];
        $rootTargetFile = $targetPath.'/'.$resource['name'].'.php';

        if ($this->files->exists($rootTargetFile) && ! $this->option('force')) {
            $this->components->warn("Skipped {$resource['name']} — {$rootTargetFile} already exists. Re-run with --force to overwrite.");

            return;
        }

        $this->writeRewrittenFile(
            $rootSourceFile,
            $rootTargetFile,
            $sourcePrefix,
            $targetPrefix,
            $targetNamespace,
            rootRewrite: ['from' => $this->sourceNamespace, 'to' => $targetNamespace],
        );

        if ($resource['dir']) {
            $this->copyDirectory($resource['dir'], $targetPath.'/'.$resource['name'], $sourcePrefix, $targetPrefix, $targetNamespace);
        }

        $this->components->info("Published {$resource['name']} → {$rootTargetFile}");
    }

    protected function copyDirectory(string $source, string $target, string $sourcePrefix, string $targetPrefix, string $targetNamespace): void
    {
        if (! $this->files->isDirectory($target)) {
            $this->files->makeDirectory($target, recursive: true);
        }

        foreach ($this->files->allFiles($source) as $file) {
            $relative = Str::after($file->getPathname(), $source.'/');
            $destination = $target.'/'.$relative;

            $destinationDir = dirname($destination);
            if (! $this->files->isDirectory($destinationDir)) {
                $this->files->makeDirectory($destinationDir, recursive: true);
            }

            if ($file->getExtension() === 'php') {
                $this->writeRewrittenFile($file->getPathname(), $destination, $sourcePrefix, $targetPrefix, $targetNamespace);
            } else {
                $this->files->copy($file->getPathname(), $destination);
            }
        }
    }

    /**
     * @param  array{from: string, to: string}|null  $rootRewrite
     */
    protected function writeRewrittenFile(string $source, string $target, string $sourcePrefix, string $targetPrefix, string $targetNamespace, ?array $rootRewrite = null): void
    {
        $contents = $this->files->get($source);
        $contents = $this->rewriteNamespaces($contents, $sourcePrefix, $targetPrefix);

        if ($rootRewrite !== null) {
            $contents = preg_replace(
                '/^namespace\s+'.preg_quote($rootRewrite['from'], '/').'\s*;/m',
                'namespace '.$rootRewrite['to'].';',
                $contents,
            ) ?? $contents;
        }

        $contents = $this->prependOwnershipNotice($contents, $targetNamespace);

        $this->files->put($target, $contents);
    }

    protected function rewriteNamespaces(string $contents, string $sourcePrefix, string $targetPrefix): string
    {
        return str_replace($sourcePrefix, $targetPrefix, $contents);
    }

    protected function prependOwnershipNotice(string $contents, string $targetNamespace): string
    {
        $notice = "// Published from lunarphp/admin via `php artisan lunar:admin:publish`.\n// Register on your panel and exclude the source class via LunarPanel::excludeResources().\n";

        return preg_replace('/^(<\?php\s*\n)/', '$1'.$notice, $contents, 1) ?? $contents;
    }
}
