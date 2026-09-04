<?php

namespace Lunar\Api\Http\Exceptions;

/** One or more query parameters are not in the resource's grammar. */
class InvalidQueryException extends ApiException
{
    /**
     * @param  array<int, array{code: string, detail: string, source: array<string, string>}>  $errors
     */
    public function __construct(protected array $errors)
    {
        parent::__construct(422, 'invalid_query', __('api::errors.invalid_query.title'));
    }

    public function toErrors(): array
    {
        return array_map(fn (array $error) => [
            'status' => '422',
            'code' => $error['code'],
            'title' => $this->getMessage(),
            'detail' => $error['detail'],
            'source' => $error['source'],
        ], $this->errors);
    }
}
