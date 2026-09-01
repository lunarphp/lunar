<?php

use Illuminate\Support\Facades\Validator;
use Lunar\Core\Rules\ValidRuleString;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('rules');

function ruleStringPasses(mixed $value): bool
{
    return Validator::make(
        ['rule' => $value],
        ['rule' => [new ValidRuleString]],
    )->passes();
}

it('accepts rule strings the validator can run', function (string $rule) {
    expect(ruleStringPasses($rule))->toBeTrue();
})->with([
    'min:3',
    'max:10',
    'email',
    'regex:/^[a-z]+$/',
    'in:red,green,blue',
    'required_if:other,yes',
]);

it('rejects rule strings the validator cannot run', function (mixed $rule) {
    expect(ruleStringPasses($rule))->toBeFalse();
})->with([
    'unknown rule name' => 'mx:10',
    'gibberish' => 'gfbfgbfsgbsf',
    'missing required parameter' => 'min',
    'malformed regex' => 'regex:/[unclosed',
    'nonexistent table' => 'exists:not_a_real_table,id',
]);

// Empty and whitespace-only strings are not covered: the validator skips
// non-implicit rules on empty input, and blank entries are trimmed to null
// and dropped before persistence in both panels regardless.

it('names the offending rule in the message', function () {
    $validator = Validator::make(
        ['rule' => 'mx:10'],
        ['rule' => [new ValidRuleString]],
    );

    expect($validator->errors()->first('rule'))->toContain('mx:10');
});
