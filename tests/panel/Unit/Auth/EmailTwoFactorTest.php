<?php

use Illuminate\Support\Facades\Notification;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Auth\EmailTwoFactor;
use Lunar\Panel\Notifications\TwoFactorEmailCode;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = app(EmailTwoFactor::class);
});

function sentCode(Staff $staff): string
{
    $code = null;

    Notification::assertSentTo($staff, TwoFactorEmailCode::class, function (TwoFactorEmailCode $notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    return $code;
}

test('generates a zero padded six digit numeric code', function () {
    $code = $this->service->generateCode();

    expect($code)->toMatch('/^\d{6}$/');
});

test('send notifies the staff member with the plaintext code and reports success', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    expect($this->service->send($staff))->toBeTrue();

    Notification::assertSentTo($staff, TwoFactorEmailCode::class);
});

test('send refuses to send again while the resend cooldown is active', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->service->send($staff);

    expect($this->service->send($staff))->toBeFalse();

    Notification::assertSentToTimes($staff, TwoFactorEmailCode::class, 1);
});

test('send succeeds again once the thirty second cooldown has elapsed', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->service->send($staff);
    $this->travel(31)->seconds();

    expect($this->service->send($staff))->toBeTrue();

    Notification::assertSentToTimes($staff, TwoFactorEmailCode::class, 2);
});

test('cooldown remaining is zero when nothing has been sent', function () {
    $staff = Staff::factory()->create();

    expect($this->service->cooldownRemaining($staff))->toBe(0);
});

test('cooldown remaining counts down from thirty seconds after a send', function () {
    Notification::fake();
    $staff = Staff::factory()->create();

    $this->service->send($staff);

    expect($this->service->cooldownRemaining($staff))->toBe(30);

    $this->travel(10)->seconds();

    expect($this->service->cooldownRemaining($staff))->toBe(20);
});

test('verify and consume accepts the correct code exactly once', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    $this->service->send($staff);
    $code = sentCode($staff);

    expect($this->service->verifyAndConsume($staff, $code))->toBeTrue()
        ->and($this->service->verifyAndConsume($staff, $code))->toBeFalse();
});

test('verify and consume rejects a wrong code without consuming the real one', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    $this->service->send($staff);
    $code = sentCode($staff);

    expect($this->service->verifyAndConsume($staff, '000000'))->toBeFalse()
        ->and($this->service->verifyAndConsume($staff, $code))->toBeTrue();
});

test('verify and consume rejects when no code has ever been sent', function () {
    $staff = Staff::factory()->create();

    expect($this->service->verifyAndConsume($staff, '123456'))->toBeFalse();
});

test('the code expires after ten minutes', function () {
    Notification::fake();
    $staff = Staff::factory()->create();
    $this->service->send($staff);
    $code = sentCode($staff);

    $this->travel(11)->minutes();

    expect($this->service->verifyAndConsume($staff, $code))->toBeFalse();
});
