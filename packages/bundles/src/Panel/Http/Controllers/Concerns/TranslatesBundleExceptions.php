<?php

namespace Lunar\Bundles\Panel\Http\Controllers\Concerns;

use Closure;
use Illuminate\Validation\ValidationException;
use Lunar\Bundles\Exceptions\BundleNesting;
use Lunar\Bundles\Exceptions\InvalidBundleDefinition;

/**
 * The actions guard the bundle invariants with typed exceptions; the editor
 * expects them as ordinary 422 validation errors under one field key.
 */
trait TranslatesBundleExceptions
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    protected function guarded(string $field, Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (BundleNesting|InvalidBundleDefinition $exception) {
            throw ValidationException::withMessages([$field => [$exception->getMessage()]]);
        }
    }
}
