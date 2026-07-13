<?php

use Illuminate\Support\Facades\Gate;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Actions\PageAction;
use Lunar\Panel\Actions\PageActionResolver;
use Lunar\Panel\Support\Position;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

class FixtureImportAction extends PageAction
{
    public function key(): string
    {
        return 'import';
    }

    public function label(): string
    {
        return 'Import';
    }

    public function position(): Position
    {
        return Position::priority(20);
    }

    public function url(mixed $context = null): ?string
    {
        return '/products/import';
    }
}

class FixtureAddAction extends PageAction
{
    public function key(): string
    {
        return 'add';
    }

    public function label(): string
    {
        return 'Add';
    }

    public function primary(): bool
    {
        return true;
    }

    public function position(): Position
    {
        return Position::priority(10);
    }
}

class FixtureSecretPageAction extends PageAction
{
    public function key(): string
    {
        return 'secret';
    }

    public function label(): string
    {
        return 'Secret';
    }

    public function permission(): ?string
    {
        return 'panel-test.secret-page-action';
    }
}

it('orders actions by position and resolves url with the given context', function () {
    $resolver = new PageActionResolver([FixtureImportAction::class, FixtureAddAction::class]);

    $actions = $resolver->resolve();

    expect(array_column($actions, 'key'))->toBe(['add', 'import'])
        ->and($actions[0]['primary'])->toBeTrue()
        ->and($actions[1]['url'])->toBe('/products/import');
});

it('filters actions by permission', function () {
    Gate::define('panel-test.secret-page-action', fn ($user) => (bool) $user->admin);

    $resolver = new PageActionResolver([FixtureSecretPageAction::class, FixtureAddAction::class]);

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');
    expect(array_column($resolver->resolve(), 'key'))->toBe(['add']);

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
    expect(array_column($resolver->resolve(), 'key'))->toContain('secret');
});
