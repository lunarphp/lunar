<?php

namespace Lunar\Api\Admin\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Lunar\Api\Admin\Http\Requests\V1\StoreApiKeyRequest;
use Lunar\Api\Admin\Resources\V1\ApiKeyResource;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\Models\ApiKey;
use Lunar\Core\Models\Staff;

class ApiKeyController extends Controller
{
    protected string $resource = ApiKeyResource::class;

    /** Issue a key. The plaintext `token` is in this response and nowhere else. */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $staff = $request->validated('staff_id')
            ? Staff::query()->wherePublicId($request->validated('staff_id'))->firstOrFail()
            : null;

        $expiresAt = $request->validated('expires_at') ? Carbon::parse($request->validated('expires_at')) : null;

        $issued = ApiKey::generate($request->validated('name'), $request->validated('abilities'), $staff, $expiresAt);

        $context = $this->context($request);
        $issued->key->load('staff');

        return Envelope::item(
            $this->definition()->serialize($issued->key, $context) + ['token' => $issued->plainTextToken],
            [],
            [],
            201,
        );
    }

    /** Revoke a key. Revoked keys stay listed so the audit trail keeps its actor. */
    public function destroy(Request $request, string $id): JsonResponse
    {
        [$key] = $this->find($request, $id);

        /** @var ApiKey $key */
        $key->revoke();

        return new JsonResponse(null, 204);
    }
}
