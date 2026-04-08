<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Lunar\Models\Country;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can import address data', function () {
    Http::fake(function ($request) {
        return Http::response([
            json_decode('{
                    "name": "United Kingdom",
                    "iso3": "GBR",
                    "iso2": "GB",
                    "numeric_code": "826",
                    "phone_code": "44",
                    "capital": "London",
                    "currency": "GBP",
                    "currency_symbol": "£",
                    "tld": ".uk",
                    "native": "United Kingdom",
                    "region": "Europe",
                    "subregion": "Northern Europe",
                    "timezones": [
                        {
                            "zoneName": "Europe\/London",
                            "gmtOffset": 0,
                            "gmtOffsetName": "UTC±00",
                            "abbreviation": "GMT",
                            "tzName": "Greenwich Mean Time"
                        }
                    ],
                    "translations": {
                        "kr": "영국",
                        "br": "Reino Unido",
                        "pt": "Reino Unido",
                        "nl": "Verenigd Koninkrijk",
                        "hr": "Ujedinjeno Kraljevstvo",
                        "fa": "بریتانیای کبیر و ایرلند شمالی",
                        "de": "Vereinigtes Königreich",
                        "es": "Reino Unido",
                        "fr": "Royaume-Uni",
                        "ja": "イギリス",
                        "it": "Regno Unito",
                        "cn": "英国"
                    },
                    "latitude": "54.00000000",
                    "longitude": "-2.00000000",
                    "emoji": "🇬🇧",
                    "emojiU": "U+1F1EC U+1F1E7",
                    "states": [
                        {
                            "id": 2463,
                            "name": "Aberdeen",
                            "state_code": "ABE",
                            "latitude": "57.14971700",
                            "longitude": "-2.09427800",
                            "type": null
                        }
                    ]
                }'),
        ], 200);
    });

    $this->artisan('lunar:import:address-data');

    $this->assertDatabaseHas('lunar_countries', [
        'name' => 'United Kingdom',
        'iso3' => 'GBR',
        'iso2' => 'GB',
        'phonecode' => '44',
        'capital' => 'London',
        'currency' => 'GBP',
        'native' => 'United Kingdom',
        'emoji' => '🇬🇧',
        'emoji_u' => 'U+1F1EC U+1F1E7',
    ]);

    $country = Country::first();

    expect($country->states)->toHaveCount(1);
});
