<?php

namespace Lunar\DemoData\Generators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;
use Lunar\DemoData\Support\DemoContext;

/**
 * Builds customers, each in the default customer group with a default
 * billing/shipping address. Roughly half are linked to a user account.
 *
 * Keyed on a `meta.demo_ref` so a re-run skips customers it already created.
 */
class CustomersGenerator implements Generator
{
    public function generate(DemoContext $context): void
    {
        $count = $context->count('customers');
        $group = $context->get('customerGroup');
        $country = $this->country();

        $created = collect();

        for ($index = 0; $index < $count; $index++) {
            $ref = 'demo-customer-'.$index;

            if ($existing = Customer::query()->where('meta->demo_ref', $ref)->first()) {
                $created->push($existing);

                continue;
            }

            $first = $context->faker->firstName();
            $last = $context->faker->lastName();
            $email = Str::slug($first.' '.$last, '.').'.'.$index.'@demo-store.test';

            $customer = Customer::create([
                'title' => $context->faker->title(),
                'first_name' => $first,
                'last_name' => $last,
                'meta' => ['demo_ref' => $ref],
            ]);

            if ($group) {
                $customer->customerGroups()->syncWithoutDetaching([$group->id]);
            }

            $customer->addresses()->create([
                'country_id' => $country?->id,
                'title' => $customer->title,
                'first_name' => $first,
                'last_name' => $last,
                'line_one' => $context->faker->buildingNumber().' '.$context->faker->streetName(),
                'city' => $context->faker->city(),
                'postcode' => Str::upper($context->faker->bothify('??## #??')),
                'contact_email' => $email,
                'contact_phone' => $context->faker->phoneNumber(),
                'shipping_default' => true,
                'billing_default' => true,
            ]);

            // Link roughly half of the customers to a user account.
            if ($index % 2 === 0) {
                $this->linkUser($customer, $first.' '.$last, $email);
            }

            $created->push($customer);
        }

        $context->set('customers', $created);
    }

    protected function country(): ?Country
    {
        return Country::query()->where('iso2', 'GB')->first()
            ?? Country::query()->first()
            ?? Country::factory()->create(['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR']);
    }

    protected function linkUser(Customer $customer, string $name, string $email): void
    {
        /** @var class-string<Model> $model */
        $model = config('auth.providers.users.model');

        $user = $model::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password')],
        );

        $customer->users()->syncWithoutDetaching([$user->getKey()]);
    }
}
