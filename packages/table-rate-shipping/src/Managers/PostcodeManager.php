<?php

namespace Lunar\Shipping\Managers;

use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Shipping\Exceptions\NoPostcodeResolverException;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;

class PostcodeManager
{
    /**
     * Registered resolvers, in registration order. Entries are either an already-resolved
     * instance or a class-string that will be resolved through the container on first use.
     *
     * @var Collection<int, string|PostcodeResolverInterface>
     */
    protected Collection $resolvers;

    public function __construct()
    {
        $this->resolvers = collect();
    }

    /**
     * Register one or more resolvers. Class strings are resolved lazily via the container.
     * When passed an array, entries are pushed in order — later entries win during matching.
     *
     * @param  string|PostcodeResolverInterface|array<int, string|PostcodeResolverInterface>  $resolver
     */
    public function addResolver(string|PostcodeResolverInterface|array $resolver): self
    {
        foreach (is_array($resolver) ? $resolver : [$resolver] as $entry) {
            $this->resolvers->push($entry);
        }

        return $this;
    }

    /**
     * Return the matching resolver for the given country. Iterates in reverse registration
     * order — the last-registered resolver that supports the country wins.
     */
    public function country(CountryContract $country): PostcodeResolverInterface
    {
        // Collection::reverse() preserves keys, so $index is the original slot in
        // $this->resolvers — which resolveInstance() relies on for in-place caching.
        foreach ($this->resolvers->reverse() as $index => $resolver) {
            $instance = $this->resolveInstance($index, $resolver);

            if ($instance->supportsCountry($country)) {
                return $instance;
            }
        }

        throw NoPostcodeResolverException::forCountry($country->iso2);
    }

    /**
     * Access the raw resolver collection — mostly for diagnostic use.
     *
     * @return Collection<int, string|PostcodeResolverInterface>
     */
    public function getResolvers(): Collection
    {
        return $this->resolvers;
    }

    /**
     * Resolve a collection entry to a concrete interface instance, caching in place so
     * subsequent calls reuse the same instance for the rest of the request.
     */
    protected function resolveInstance(int $index, string|PostcodeResolverInterface $resolver): PostcodeResolverInterface
    {
        if ($resolver instanceof PostcodeResolverInterface) {
            return $resolver;
        }

        $instance = app()->make($resolver);

        if (! $instance instanceof PostcodeResolverInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Postcode resolver [%s] must implement %s.',
                $resolver,
                PostcodeResolverInterface::class
            ));
        }

        $this->resolvers->put($index, $instance);

        return $instance;
    }
}
