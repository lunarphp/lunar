<?php

namespace Lunar\Api\Console;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Http\Controllers\SchemaController;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Support\Superuser;

class SchemaCommand extends Command
{
    protected $signature = 'lunar:api:schema
        {surface=storefront : storefront or admin}
        {--api-version=v1 : The surface version}
        {--json : Print the schema as JSON}';

    protected $description = 'Print the resources, fields, includes, filters, sorts and routes an API surface serves';

    public function handle(ApiManager $api, Router $router): int
    {
        $registry = $api->surface($this->argument('surface'), (string) $this->option('api-version'));
        $schema = SchemaController::describe($registry, new SerializationContext($registry, principal: new Superuser), $router);

        if ($this->option('json')) {
            $this->line(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info("{$schema['surface']} {$schema['version']}");

        foreach ($schema['resources'] as $resource) {
            $this->newLine();
            $this->components->twoColumnDetail("<fg=yellow>{$resource['type']}</>");
            $this->components->twoColumnDetail('fields', implode(', ', array_column($resource['fields'], 'name')) ?: '-');
            $this->components->twoColumnDetail('includes', implode(', ', array_column($resource['includes'], 'name')) ?: '-');
            $this->components->twoColumnDetail('filters', implode(', ', array_map(
                fn (array $filter) => $filter['name'].' ['.implode(',', $filter['operators']).']',
                $resource['filters'],
            )) ?: '-');
            $this->components->twoColumnDetail('sorts', implode(', ', array_column($resource['sorts'], 'name')) ?: '-');

            foreach ($resource['routes'] as $route) {
                $this->components->twoColumnDetail("  {$route['method']} {$route['uri']}", $route['name']);
            }
        }

        return self::SUCCESS;
    }
}
