<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Lunar\Api\Http\Exceptions\ApiException;
use Lunar\Api\Http\Exceptions\ErrorRenderer;
use Lunar\Tests\Api\TestCase;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

uses(TestCase::class);

test('framework exceptions map onto error objects', function (): void {
    $renderer = new ErrorRenderer(debug: false);

    $validation = null;

    try {
        Validator::make(['quantity' => 0], ['quantity' => 'integer|min:1', 'lines.0.sku' => 'required'])->validate();
    } catch (ValidationException $e) {
        $validation = $e;
    }

    $response = $renderer->render($validation);

    expect($response->getStatusCode())->toBe(422);
    $errors = $response->getData(true)['errors'];

    expect($errors[0])->toMatchArray(['status' => '422', 'code' => 'validation_failed']);
    expect(array_column(array_column($errors, 'source'), 'pointer'))->toEqualCanonicalizing(['/quantity', '/lines/0/sku']);

    expect($renderer->render(new AuthenticationException)->getStatusCode())->toBe(401);
    expect($renderer->render(new AuthorizationException)->getStatusCode())->toBe(403);
    expect($renderer->render(new ModelNotFoundException)->getStatusCode())->toBe(404);

    $throttled = $renderer->render(new TooManyRequestsHttpException(30));

    expect($throttled->getStatusCode())->toBe(429);
    expect($throttled->headers->get('Retry-After'))->toBe('30');

    $server = $renderer->render(new RuntimeException('secret detail'));

    expect($server->getStatusCode())->toBe(500);
    expect($server->getData(true)['errors'][0]['detail'])->not->toContain('secret');

    expect((new ErrorRenderer(debug: true))->render(new RuntimeException('secret detail'))->getData(true)['errors'][0]['detail'])
        ->toBe('secret detail');
});

test('api exceptions carry their code, detail and source', function (): void {
    $response = (new ErrorRenderer)->render(ApiException::notFound('products', 'abc'));

    expect($response->getStatusCode())->toBe(404);
    expect($response->getData(true)['errors'][0])->toMatchArray([
        'status' => '404',
        'code' => 'resource_not_found',
        'title' => 'Not found',
        'detail' => 'No products resource with id "abc" exists.',
    ]);
});
