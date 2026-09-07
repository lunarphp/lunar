<?php

namespace Lunar\Api\Console;

use Illuminate\Console\Command;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\OpenApi\Generator;
use Symfony\Component\Yaml\Yaml;

class OpenApiCommand extends Command
{
    protected $signature = 'lunar:api:openapi
        {surface=storefront : storefront or admin}
        {--api-version=v1 : The surface version}
        {--out= : Write the document to this file instead of stdout}
        {--yaml : Emit YAML instead of JSON (needs symfony/yaml)}';

    protected $description = 'Generate the OpenAPI 3.1 document of an API surface';

    public function handle(ApiManager $api, Generator $generator): int
    {
        $surface = (string) $this->argument('surface');
        $version = (string) $this->option('api-version');
        $surfaces = $api->surfaces();

        if (! isset($surfaces["{$surface}:{$version}"])) {
            $this->components->error("No API surface [{$surface} {$version}] is registered. Available: ".implode(', ', array_keys($surfaces)).'.');

            return self::FAILURE;
        }

        $document = $generator->generate($surfaces["{$surface}:{$version}"]);

        if ($this->option('yaml')) {
            if (! class_exists(Yaml::class)) {
                $this->components->error('The --yaml option needs symfony/yaml: composer require symfony/yaml.');

                return self::FAILURE;
            }

            $output = Yaml::dump($document->toArray(), 32, 2, Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        } else {
            $output = $document->toJson(JSON_PRETTY_PRINT)."\n";
        }

        if ($path = $this->option('out')) {
            $directory = dirname((string) $path);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents((string) $path, $output);
            $this->components->info("Wrote {$surface} {$version} to {$path}.");

            return self::SUCCESS;
        }

        $this->output->write($output);

        return self::SUCCESS;
    }
}
