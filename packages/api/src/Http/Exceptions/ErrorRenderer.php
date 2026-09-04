<?php

namespace Lunar\Api\Http\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Maps every exception that can escape an API route onto the JSON:API error
 * envelope, so consumers see one shape whether the failure came from the
 * grammar, validation, auth or the framework.
 */
final class ErrorRenderer
{
    public function __construct(private readonly bool $debug = false) {}

    public function render(Throwable $e): JsonResponse
    {
        if ($e instanceof ApiException) {
            return $this->respond($e->status(), $e->toErrors(), $e->headers());
        }

        if ($e instanceof ValidationException) {
            $errors = [];

            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = $this->error(422, 'validation_failed', __('api::errors.validation_failed.title'), $message, [
                        'pointer' => '/'.str_replace('.', '/', $field),
                    ]);
                }
            }

            return $this->respond(422, $errors);
        }

        if ($e instanceof AuthenticationException) {
            return $this->respond(401, [$this->translated(401, 'unauthenticated')]);
        }

        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return $this->respond(403, [$this->translated(403, 'forbidden')]);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $this->respond(404, [$this->translated(404, 'not_found')]);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->respond(405, [$this->translated(405, 'method_not_allowed')], $e->getHeaders());
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return $this->respond(429, [$this->translated(429, 'too_many_requests')], $e->getHeaders());
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $title = Response::$statusTexts[$status] ?? 'Error';

            return $this->respond($status, [$this->error($status, 'http_error', $title, $e->getMessage() ?: null)], $e->getHeaders());
        }

        return $this->respond(500, [
            $this->error(500, 'server_error', __('api::errors.server_error.title'), $this->debug ? $e->getMessage() : __('api::errors.server_error.detail')),
        ]);
    }

    /** @return array<string, mixed> */
    private function translated(int $status, string $code): array
    {
        return $this->error($status, $code, __("api::errors.{$code}.title"), __("api::errors.{$code}.detail"));
    }

    /**
     * @param  array<string, string>|null  $source
     * @return array<string, mixed>
     */
    private function error(int $status, string $code, string $title, ?string $detail = null, ?array $source = null): array
    {
        return array_filter([
            'status' => (string) $status,
            'code' => $code,
            'title' => $title,
            'detail' => $detail,
            'source' => $source,
        ], fn ($value) => $value !== null);
    }

    /**
     * @param  array<int, array<string, mixed>>  $errors
     * @param  array<string, string>  $headers
     */
    private function respond(int $status, array $errors, array $headers = []): JsonResponse
    {
        return new JsonResponse(['errors' => $errors], $status, $headers);
    }
}
