<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Models\Country;
use Lunar\Shipping\Exceptions\NoPostcodeResolverException;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;
use Lunar\Shipping\Managers\PostcodeManager;
use Lunar\Shipping\Resolvers\PostcodeResolver;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)
    ->group('shipping', 'shipping-postcode');
uses(RefreshDatabase::class);

test('addResolver pushes the resolver onto the collection', function () {
    $manager = new PostcodeManager;

    $manager->addResolver(PostcodeResolver::class);

    expect($manager->getResolvers())->toBeInstanceOf(Collection::class);
    expect($manager->getResolvers())->toHaveCount(1);
});

test('addResolver returns the manager for fluent chaining', function () {
    $manager = new PostcodeManager;

    expect($manager->addResolver(PostcodeResolver::class))->toBe($manager);
});

test('addResolver accepts an array and registers each entry in order', function () {
    $instance = new class implements PostcodeResolverInterface
    {
        public function supportsCountry(CountryContract $country): bool
        {
            return true;
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect([$postcode]);
        }
    };

    $manager = new PostcodeManager;
    $manager->addResolver([PostcodeResolver::class, $instance]);

    expect($manager->getResolvers())->toHaveCount(2);
    expect($manager->getResolvers()->all())->toBe([PostcodeResolver::class, $instance]);
});

test('addResolver array registration preserves last-wins matching order', function () {
    $gb = Country::factory()->create(['iso2' => 'GB']);
    $us = Country::factory()->create(['iso2' => 'US']);

    $gbOnly = new class implements PostcodeResolverInterface
    {
        public string $label = 'gb-only';

        public function supportsCountry(CountryContract $country): bool
        {
            return $country->iso2 === 'GB';
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect([$postcode]);
        }
    };

    $manager = new PostcodeManager;
    $manager->addResolver([PostcodeResolver::class, $gbOnly]);

    expect($manager->country($gb))->toBe($gbOnly);
    expect($manager->country($us))->toBeInstanceOf(PostcodeResolver::class);
});

test('country returns the last-registered matching resolver', function () {
    $gb = Country::factory()->create(['iso2' => 'GB']);

    $resolverA = new class implements PostcodeResolverInterface
    {
        public string $label = 'A';

        public function supportsCountry(CountryContract $country): bool
        {
            return $country->iso2 === 'GB';
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect([$postcode]);
        }
    };

    $resolverB = new class implements PostcodeResolverInterface
    {
        public string $label = 'B';

        public function supportsCountry(CountryContract $country): bool
        {
            return $country->iso2 === 'GB';
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect([$postcode]);
        }
    };

    $manager = new PostcodeManager;
    $manager->addResolver($resolverA);
    $manager->addResolver($resolverB);

    expect($manager->country($gb)->label)->toBe('B');
});

test('country falls through to an earlier resolver when later ones do not support it', function () {
    $us = Country::factory()->create(['iso2' => 'US']);

    $gbOnly = new class implements PostcodeResolverInterface
    {
        public function supportsCountry(CountryContract $country): bool
        {
            return $country->iso2 === 'GB';
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect([$postcode]);
        }
    };

    $manager = new PostcodeManager;
    $manager->addResolver(PostcodeResolver::class); // catch-all, registered first
    $manager->addResolver($gbOnly);                 // GB-only, registered last

    expect($manager->country($us))->toBeInstanceOf(PostcodeResolver::class);
});

test('country throws when no resolver claims the country', function () {
    $fr = Country::factory()->create(['iso2' => 'FR']);

    $manager = new PostcodeManager; // no resolvers registered

    $manager->country($fr);
})->throws(NoPostcodeResolverException::class, 'FR');

test('country resolves class-string registrations via the container on first use', function () {
    $gb = Country::factory()->create(['iso2' => 'GB']);

    $manager = new PostcodeManager;
    $manager->addResolver(PostcodeResolver::class);

    $resolved = $manager->country($gb);

    expect($resolved)->toBeInstanceOf(PostcodeResolver::class);
});

test('country rejects a registered class that does not implement the interface', function () {
    $gb = Country::factory()->create(['iso2' => 'GB']);

    $manager = new PostcodeManager;
    $manager->addResolver(stdClass::class);

    $manager->country($gb);
})->throws(
    InvalidArgumentException::class,
    PostcodeResolverInterface::class
);
