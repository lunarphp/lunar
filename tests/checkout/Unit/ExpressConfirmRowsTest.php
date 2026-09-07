<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Express confirm row keys (spec 0013 §F)
|--------------------------------------------------------------------------
|
| The squeeze page's inline editors are keyed by a single string: `editing(k)`
| adds the `.editing` class the stylesheet needs to reveal `.xc-row-edit`, and
| `openEdit(k)` sets it. A key opened but never tested renders a Change button
| that opens nothing, which is how the collect row's branch picker became
| unreachable. Asserted against the source because the package carries no JS
| test runner.
|
*/

it('only opens inline editors the template can reveal', function () {
    $source = file_get_contents(__DIR__.'/../../../packages/checkout/resources/js/pages/ExpressConfirm.vue');

    preg_match_all("/openEdit\('([a-z-]+)'\)/", $source, $opened);
    preg_match_all("/editing\('([a-z-]+)'\)/", $source, $tested);

    expect(array_unique($opened[1]))->not->toBeEmpty()
        ->and(array_values(array_diff(array_unique($opened[1]), $tested[1])))->toBe([]);
});

it('keys the collect row on the pickup editor', function () {
    $source = file_get_contents(__DIR__.'/../../../packages/checkout/resources/js/pages/ExpressConfirm.vue');

    expect($source)->toContain("openEdit('pickup')")
        ->and($source)->toContain("editing('pickup')");
});
