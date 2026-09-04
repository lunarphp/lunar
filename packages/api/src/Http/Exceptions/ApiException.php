<?php

namespace Lunar\Api\Http\Exceptions;

use RuntimeException;

/**
 * An error the API renders as a JSON:API error object. Titles and details are
 * translated `api::errors.*` strings.
 */
class ApiException extends RuntimeException
{
    /**
     * @param  array<string, string>|null  $source  `['parameter' => ...]`, `['pointer' => ...]` or `['header' => ...]`
     * @param  array<string, string>  $headers  extra response headers
     */
    public function __construct(
        protected int $status,
        protected string $errorCode,
        string $title,
        protected ?string $detail = null,
        protected ?array $source = null,
        protected array $headers = [],
    ) {
        parent::__construct($title);
    }

    /** @param  array<string, mixed>  $replace */
    public static function make(int $status, string $code, array $replace = [], ?array $source = null): self
    {
        return new self(
            $status,
            $code,
            __("api::errors.{$code}.title"),
            self::translateDetail($code, $replace),
            $source,
        );
    }

    public static function notFound(string $type, string $id): self
    {
        return self::make(404, 'resource_not_found', ['type' => $type, 'id' => $id]);
    }

    public static function invalidHeader(string $header, mixed $value): self
    {
        return self::make(422, 'invalid_header', ['header' => $header, 'value' => (string) $value], ['header' => $header]);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    /** @return array<int, array<string, mixed>> */
    public function toErrors(): array
    {
        return [array_filter([
            'status' => (string) $this->status,
            'code' => $this->errorCode,
            'title' => $this->getMessage(),
            'detail' => $this->detail,
            'source' => $this->source,
        ], fn ($value) => $value !== null)];
    }

    /** @param  array<string, mixed>  $replace */
    protected static function translateDetail(string $code, array $replace): ?string
    {
        $key = "api::errors.{$code}.detail";
        $detail = __($key, $replace);

        return $detail === $key ? null : $detail;
    }
}
