<?php

use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Clusters\Taxes;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('cluster.taxes');

it('extends filament cluster', function () {
    $reflection = new ReflectionClass(Taxes::class);
    expect($reflection->isSubclassOf(\Filament\Clusters\Cluster::class))->toBeTrue();
});

it('has correct navigation sort order', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $navigationSortProperty = $reflection->getProperty('navigationSort');
    $navigationSortProperty->setAccessible(true);

    expect($navigationSortProperty->getValue())->toBe(5);
});

it('returns correct navigation group translation key', function () {
    $navigationGroup = Taxes::getNavigationGroup();

    expect($navigationGroup)->toBe('lunarpanel::global.sections.settings');
});

it('navigation group returns string type', function () {
    $navigationGroup = Taxes::getNavigationGroup();

    expect($navigationGroup)->toBeString();
});

it('navigation group method allows null return type', function () {
    $reflection = new ReflectionMethod(Taxes::class, 'getNavigationGroup');
    $returnType = $reflection->getReturnType();

    expect($returnType->allowsNull())->toBeTrue();
});

it('returns correct navigation label translation key', function () {
    $navigationLabel = Taxes::getNavigationLabel();

    expect($navigationLabel)->toBe('lunarpanel::tax.plural_label');
});

it('navigation label returns string type', function () {
    $navigationLabel = Taxes::getNavigationLabel();

    expect($navigationLabel)->toBeString();
});

it('navigation label never returns null', function () {
    $navigationLabel = Taxes::getNavigationLabel();

    expect($navigationLabel)->not->toBeNull();
});

it('returns correct cluster breadcrumb translation key', function () {
    $breadcrumb = Taxes::getClusterBreadcrumb();

    expect($breadcrumb)->toBe('lunarpanel::tax.plural_label');
});

it('cluster breadcrumb returns string type', function () {
    $breadcrumb = Taxes::getClusterBreadcrumb();

    expect($breadcrumb)->toBeString();
});

it('cluster breadcrumb method allows null return type', function () {
    $reflection = new ReflectionMethod(Taxes::class, 'getClusterBreadcrumb');
    $returnType = $reflection->getReturnType();

    expect($returnType->allowsNull())->toBeTrue();
});

it('resolves navigation icon through FilamentIcon facade', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn('heroicon-o-calculator');

    $icon = Taxes::getNavigationIcon();

    expect($icon)->toBe('heroicon-o-calculator');
});

it('navigation icon returns string when FilamentIcon returns string', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn('heroicon-o-calculator');

    $icon = Taxes::getNavigationIcon();

    expect($icon)->toBeString();
});

it('navigation icon handles null return from FilamentIcon', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn(null);

    $icon = Taxes::getNavigationIcon();

    expect($icon)->toBeNull();
});

it('navigation icon method allows null return type', function () {
    $reflection = new ReflectionMethod(Taxes::class, 'getNavigationIcon');
    $returnType = $reflection->getReturnType();

    expect($returnType->allowsNull())->toBeTrue();
});

it('all public methods are static', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        if ($method->getDeclaringClass()->getName() === Taxes::class) {
            expect($method->isStatic())->toBeTrue("Method {$method->getName()} should be static");
        }
    }
});

it('has all expected public methods', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $expectedMethods = [
        'getNavigationGroup',
        'getNavigationLabel',
        'getClusterBreadcrumb',
        'getNavigationIcon',
    ];

    foreach ($expectedMethods as $expectedMethod) {
        expect($reflection->hasMethod($expectedMethod))
            ->toBeTrue("Class should have method: {$expectedMethod}");
    }
});

it('navigation sort property is protected and static', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $property = $reflection->getProperty('navigationSort');

    expect($property->isProtected())->toBeTrue();
    expect($property->isStatic())->toBeTrue();
});

it('navigation sort is integer type', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $property = $reflection->getProperty('navigationSort');
    $property->setAccessible(true);

    $value = $property->getValue();
    expect($value)->toBeInt();
});

it('navigation methods return expected translation keys', function () {
    $expectedTranslations = [
        'getNavigationGroup' => 'lunarpanel::global.sections.settings',
        'getNavigationLabel' => 'lunarpanel::tax.plural_label',
        'getClusterBreadcrumb' => 'lunarpanel::tax.plural_label',
    ];

    foreach ($expectedTranslations as $method => $expectedKey) {
        $result = Taxes::$method();
        expect($result)->toBe($expectedKey, "Method {$method} should return translation key: {$expectedKey}");
    }
});

it('calls FilamentIcon resolve with correct parameter', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn('test-icon');

    Taxes::getNavigationIcon();
});

it('navigation sort value is within reasonable range', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $property = $reflection->getProperty('navigationSort');
    $property->setAccessible(true);

    $value = $property->getValue();
    expect($value)->toBeGreaterThanOrEqual(0);
    expect($value)->toBeLessThanOrEqual(100);
});

it('translation keys follow expected lunarpanel pattern', function () {
    $navigationGroup = Taxes::getNavigationGroup();
    $navigationLabel = Taxes::getNavigationLabel();
    $breadcrumb = Taxes::getClusterBreadcrumb();

    expect($navigationGroup)->toStartWith('lunarpanel::');
    expect($navigationLabel)->toStartWith('lunarpanel::');
    expect($breadcrumb)->toStartWith('lunarpanel::');
});

it('icon key follows expected lunar pattern', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn('test-icon');

    $icon = Taxes::getNavigationIcon();

    // Verify the icon key passed to resolve follows lunar:: pattern
    // This is implicitly tested by the mock expectation above
    expect($icon)->not->toBeNull();
});

it('navigation group and breadcrumb use same translation key as navigation label', function () {
    $navigationLabel = Taxes::getNavigationLabel();
    $breadcrumb = Taxes::getClusterBreadcrumb();

    expect($breadcrumb)->toBe($navigationLabel);
});

it('class can be instantiated as cluster', function () {
    $taxesCluster = new class extends Taxes {};

    expect($taxesCluster)->toBeInstanceOf(\Filament\Clusters\Cluster::class);
});

it('methods return consistent values across multiple calls', function () {
    $firstNavigationGroup = Taxes::getNavigationGroup();
    $secondNavigationGroup = Taxes::getNavigationGroup();

    $firstNavigationLabel = Taxes::getNavigationLabel();
    $secondNavigationLabel = Taxes::getNavigationLabel();

    $firstBreadcrumb = Taxes::getClusterBreadcrumb();
    $secondBreadcrumb = Taxes::getClusterBreadcrumb();

    expect($secondNavigationGroup)->toBe($firstNavigationGroup);
    expect($secondNavigationLabel)->toBe($firstNavigationLabel);
    expect($secondBreadcrumb)->toBe($firstBreadcrumb);
});

it('translation keys contain expected segments', function () {
    $navigationGroup = Taxes::getNavigationGroup();
    $navigationLabel = Taxes::getNavigationLabel();
    $breadcrumb = Taxes::getClusterBreadcrumb();

    expect($navigationGroup)->toContain('global.sections.settings');
    expect($navigationLabel)->toContain('tax.plural_label');
    expect($breadcrumb)->toContain('tax.plural_label');
});

it('handles FilamentIcon facade properly when icon exists', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn('heroicon-m-receipt-tax');

    $icon = Taxes::getNavigationIcon();

    expect($icon)->toBe('heroicon-m-receipt-tax');
    expect($icon)->toContain('heroicon');
});

it('handles FilamentIcon facade properly when icon does not exist', function () {
    FilamentIcon::shouldReceive('resolve')
        ->once()
        ->with('lunar::tax')
        ->andReturn(null);

    $icon = Taxes::getNavigationIcon();

    expect($icon)->toBeNull();
});

it('class follows PSR-4 naming convention', function () {
    $reflection = new ReflectionClass(Taxes::class);

    expect($reflection->getName())->toBe('Lunar\Admin\Filament\Clusters\Taxes');
    expect($reflection->getShortName())->toBe('Taxes');
});

it('class properties have correct visibility and modifiers', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $properties = $reflection->getProperties();

    foreach ($properties as $property) {
        if ($property->getName() === 'navigationSort') {
            expect($property->isProtected())->toBeTrue();
            expect($property->isStatic())->toBeTrue();
        }
    }
});

it('navigation sort has correct data type annotation', function () {
    $reflection = new ReflectionClass(Taxes::class);
    $property = $reflection->getProperty('navigationSort');
    $property->setAccessible(true);

    $value = $property->getValue();
    expect($value)->toBeInt();
    expect($value)->toBe(5);
});

it('methods have correct return type declarations', function () {
    $reflection = new ReflectionClass(Taxes::class);

    $getNavigationGroupMethod = $reflection->getMethod('getNavigationGroup');
    expect($getNavigationGroupMethod->getReturnType()->getName())->toBe('string');
    expect($getNavigationGroupMethod->getReturnType()->allowsNull())->toBeTrue();

    $getNavigationLabelMethod = $reflection->getMethod('getNavigationLabel');
    expect($getNavigationLabelMethod->getReturnType()->getName())->toBe('string');
    expect($getNavigationLabelMethod->getReturnType()->allowsNull())->toBeFalse();

    $getClusterBreadcrumbMethod = $reflection->getMethod('getClusterBreadcrumb');
    expect($getClusterBreadcrumbMethod->getReturnType()->getName())->toBe('string');
    expect($getClusterBreadcrumbMethod->getReturnType()->allowsNull())->toBeTrue();

    $getNavigationIconMethod = $reflection->getMethod('getNavigationIcon');
    expect($getNavigationIconMethod->getReturnType()->getName())->toBe('string');
    expect($getNavigationIconMethod->getReturnType()->allowsNull())->toBeTrue();
});
