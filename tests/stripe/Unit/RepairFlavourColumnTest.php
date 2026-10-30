<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(TestCase::class);

function repairFlavourMigration(): object
{
    return require __DIR__.'/../../../packages/stripe/database/migrations/2026_10_30_000000_add_flavour_to_stripe_payment_intents_table.php';
}

it('adds flavour to a table created before the create migration was edited', function () {
    Schema::table('lunar_stripe_payment_intents', fn (Blueprint $table) => $table->dropColumn('flavour'));

    repairFlavourMigration()->up();

    $cart = CartBuilder::build();
    $cart->paymentIntents()->create(['intent_id' => 'PI_OLD_DB', 'status' => 'requires_payment_method']);

    expect(Schema::hasColumn('lunar_stripe_payment_intents', 'flavour'))->toBeTrue()
        ->and(Stripe::getCartIntentId($cart))->toBe('PI_OLD_DB');
});

it('leaves a table that already has flavour alone', function () {
    repairFlavourMigration()->up();

    expect(Schema::hasColumn('lunar_stripe_payment_intents', 'flavour'))->toBeTrue();
});
