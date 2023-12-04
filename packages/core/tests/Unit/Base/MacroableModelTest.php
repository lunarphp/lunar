<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Product;

beforeEach(function () {
    $this->model = new Product();
});

test('can register a new macro', function () {
    $this->model::macro('newMethod', function () {
        return 'newValue';
    });

    expect($this->model->newMethod())->toEqual('newValue');
    expect($this->model::newMethod())->toEqual('newValue');
});

test('can register a new macro and be invoked', function () {
    $this->model::macro('newMethod', new class()
    {
        function __invoke()
        {
            return 'newValue';
        }
    });

    expect($this->model->newMethod())->toEqual('newValue');
    expect($this->model::newMethod())->toEqual('newValue');
});
