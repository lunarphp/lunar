<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Support\Position;
use Lunar\Panel\Tables\Resolvers\TableExtensionResolver;
use Lunar\Panel\Tables\Support\ColumnType;
use Lunar\Panel\Tables\TableAction;
use Lunar\Panel\Tables\TableColumn;
use Lunar\Panel\Tables\TableExtension;
use Lunar\Panel\Tables\TableFilter;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

class FixtureRatingColumn extends TableColumn
{
    public function key(): string
    {
        return 'rating';
    }

    public function header(): string
    {
        return 'Rating';
    }

    public function type(): ?ColumnType
    {
        return ColumnType::badge();
    }

    public function position(): Position
    {
        return Position::after('name');
    }

    public function query(Builder $query): void
    {
        $query->selectRaw('1 as rating');
    }
}

class FixtureSecretColumn extends TableColumn
{
    public function key(): string
    {
        return 'secret';
    }

    public function header(): string
    {
        return 'Secret';
    }

    public function permission(): ?string
    {
        return 'panel-test.table';
    }
}

class FixtureAdminFilter extends TableFilter
{
    public function key(): string
    {
        return 'is_admin';
    }

    public function query(Builder $query, mixed $value): void
    {
        $query->where('admin', (bool) $value);
    }
}

class FixturePingAction extends TableAction
{
    public function key(): string
    {
        return 'ping';
    }

    public function label(): string
    {
        return 'Ping';
    }
}

class FixtureTableExtension extends TableExtension
{
    public function searchQuery(Builder $query, string $term): void
    {
        $query->orWhere('email', 'like', "%{$term}%");
    }

    public function columns(): array
    {
        return [FixtureRatingColumn::class, FixtureSecretColumn::class];
    }

    public function filters(): array
    {
        return [FixtureAdminFilter::class];
    }

    public function actions(): array
    {
        return [FixturePingAction::class];
    }
}

it('collects columns, filters and actions from extension classes', function () {
    $resolver = new TableExtensionResolver([FixtureTableExtension::class]);

    expect(array_column($resolver->getColumns(), 'key'))->toContain('rating')
        ->and($resolver->getColumns()[0]['type'])->toBe(['name' => 'badge', 'options' => []])
        ->and($resolver->getColumns()[0]['position'])->toBe(['type' => 'after', 'reference' => 'name'])
        ->and(array_column($resolver->getFilters(), 'key'))->toBe(['is_admin'])
        ->and(array_column($resolver->getActions(), 'key'))->toBe(['ping']);
});

it('hides columns whose permission the user lacks', function () {
    Gate::define('panel-test.table', fn ($user) => (bool) $user->admin);

    $resolver = new TableExtensionResolver([FixtureTableExtension::class]);

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');
    expect(array_column($resolver->getColumns(), 'key'))->not->toContain('secret');
});

it('shows permission-gated columns to authorised users', function () {
    Gate::define('panel-test.table', fn ($user) => (bool) $user->admin);

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $resolver = new TableExtensionResolver([FixtureTableExtension::class]);
    expect(array_column($resolver->getColumns(), 'key'))->toContain('secret');
});

it('applies filters from request input', function () {
    $resolver = new TableExtensionResolver([FixtureTableExtension::class]);
    $request = Request::create('/x', 'GET', ['filter' => ['is_admin' => '1']]);

    $query = Staff::query();
    $resolver->applyFilters($query, $request);

    expect($query->toSql())->toContain('"admin"');
});

it('skips filters with empty values', function () {
    $resolver = new TableExtensionResolver([FixtureTableExtension::class]);
    $request = Request::create('/x', 'GET', ['filter' => ['is_admin' => '']]);

    $query = Staff::query();
    $resolver->applyFilters($query, $request);

    expect($query->toSql())->not->toContain('"admin"');
});

it('applies search queries from every extension', function () {
    $resolver = new TableExtensionResolver([FixtureTableExtension::class]);

    $query = Staff::query()->where('id', '>', 0);
    $resolver->applySearchQueries($query, 'glenn');

    expect($query->toSql())->toContain('"email"');
});
