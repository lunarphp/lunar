<?php

namespace Lunar\Api\Admin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Lunar\Api\Models\ApiKey;

/**
 * The `lunar-api-key` driver: resolves the bearer token to an active API key.
 */
class ApiKeyGuard implements Guard
{
    use GuardHelpers;

    public function __construct(protected Request $request) {}

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->request->bearerToken();

        if (! $token) {
            return null;
        }

        return $this->user = ApiKey::findActiveByToken($token);
    }

    public function validate(array $credentials = []): bool
    {
        $token = $credentials['token'] ?? null;

        return is_string($token) && ApiKey::findActiveByToken($token) !== null;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;
        $this->user = null;

        return $this;
    }
}
