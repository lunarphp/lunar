<?php

use Illuminate\Support\Facades\Log;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\SectionExtension;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Panel\Tables\TableExtension;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('is a container singleton behind the facade', function () {
    expect(app(PanelManager::class))->toBe(app(PanelManager::class))
        ->and(Panel::getFacadeRoot())->toBe(app(PanelManager::class));
});

it('seeds the dashboard navigation item at boot', function () {
    $items = app(PanelManager::class)->navigation()->toArray()['items'];

    expect(array_column($items, 'key'))->toContain('dashboard');
});

it('processes a section navigation, slots and table extensions', function () {
    $section = new class extends Section
    {
        public function key(): string
        {
            return 'testing';
        }

        public function navigation(NavigationRegistry $registry): void
        {
            $registry->group('testing-group', 'Testing');
            $registry->addItem('testing-group', new NavigationItem(key: 'widgets', label: 'Widgets'));
        }

        public function slots(SlotRegistry $registry): void
        {
            $registry->add(new Slot(zone: 'widgets.index:main', component: 'testing::Banner'));
        }

        public function tableExtensions(): array
        {
            return ['widgets.index' => TableExtension::class];
        }
    };

    Panel::section($section);

    $manager = app(PanelManager::class);
    $manager->processSections();

    expect(array_column($manager->navigation()->toArray()['groups'], 'key'))->toContain('testing-group')
        ->and($manager->slots()->forPage('widgets.index'))->toHaveKey('widgets.index:main')
        ->and($manager->getTableExtensions('widgets.index'))->toContain(TableExtension::class);
});

it('applies extensions to their target section', function () {
    $section = new class extends Section
    {
        public function key(): string
        {
            return 'testing';
        }
    };

    $extension = new class extends SectionExtension
    {
        public function extends(): string
        {
            return 'testing';
        }

        public function slots(SlotRegistry $registry): void
        {
            $registry->add(new Slot(zone: 'widgets.show:main:after', component: 'addon::Extra'));
        }
    };

    Panel::section($section);
    Panel::extendSection($extension);
    app(PanelManager::class)->processSections();

    expect(app(PanelManager::class)->slots()->forPage('widgets.show'))
        ->toHaveKey('widgets.show:main:after');
});

it('logs a warning when an extension targets an unknown section', function () {
    Log::spy();

    Panel::extendSection(new class extends SectionExtension
    {
        public function extends(): string
        {
            return 'missing-section';
        }
    });

    app(PanelManager::class)->processSections();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message) => str_contains($message, 'missing-section'))
        ->once();
});

it('falls back to the staff guard when no panel guard is configured', function () {
    config()->set('lunar.panel.guard', null);
    config()->set('lunar.staff.guard', 'staff');

    expect(app(PanelManager::class)->guard())->toBe('staff');

    config()->set('lunar.panel.guard', 'web');
    expect(app(PanelManager::class)->guard())->toBe('web');
});

it('normalises vite registrations', function () {
    $manager = app(PanelManager::class);
    $manager->vite('addon', 'resources/js/app.ts');

    expect($manager->registeredVites()['addon'])->toBe([
        'hotFile' => null,
        'buildDirectory' => 'build',
        'input' => 'resources/js/app.ts',
    ]);
});
