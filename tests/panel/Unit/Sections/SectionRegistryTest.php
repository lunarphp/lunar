<?php

use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\SectionExtension;
use Lunar\Panel\Sections\SectionRegistry;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function makeSection(string $key): Section
{
    return new class($key) extends Section
    {
        public function __construct(private string $sectionKey) {}

        public function key(): string
        {
            return $this->sectionKey;
        }
    };
}

it('registers sections by key', function () {
    $registry = new SectionRegistry;
    $registry->register(makeSection('catalog'));

    expect($registry->has('catalog'))->toBeTrue()
        ->and($registry->get('catalog'))->toBeInstanceOf(Section::class)
        ->and($registry->has('missing'))->toBeFalse();
});

it('maps extensions to their target section key', function () {
    $extension = new class extends SectionExtension
    {
        public function extends(): string
        {
            return 'catalog';
        }
    };

    $registry = new SectionRegistry;
    $registry->registerExtension($extension);

    expect($registry->extensions())->toHaveKey('catalog')
        ->and($registry->extensions()['catalog'])->toHaveCount(1);
});

it('defaults the section label to the ucfirst key', function () {
    expect(makeSection('catalog')->label())->toBe('Catalog');
});

it('has empty default hooks', function () {
    $section = makeSection('catalog');

    expect($section->routes())->toBeNull()
        ->and($section->tableExtensions())->toBe([])
        ->and($section->vite())->toBeNull();
});
