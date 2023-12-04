<?php

namespace Lunar\Admin\Support\ActivityLog\Orders;

use Lunar\Admin\Support\ActivityLog\AbstractRender;
use Lunar\Models\Country;
use Spatie\Activitylog\Models\Activity;
use Spatie\LaravelBlink\BlinkFacade;

class Address extends AbstractRender
{
    public function getEvent(): string
    {
        return 'order-address-update';
    }

    public function render(Activity $log)
    {
        $type = $log->getExtraProperty('type') ?? 'shipping';
        $fields = $log->getExtraProperty('fields');
        $previousAddress = $log->getExtraProperty('previous');
        $newAddress = $log->getExtraProperty('new');

        $diff = [];

        $getCountryName = fn ($countryId) => BlinkFacade::once('lunar_activitylog_country_'.$countryId,
            fn () => Country::whereId($countryId)->first()?->name ?? $countryId);

        foreach ($fields as $field) {
            $old = $previousAddress[$field] ?? null;
            $new = $newAddress[$field] ?? null;

            if (blank($old) && blank($new)) {
                continue;
            }

            if ($old != $new) {
                if ($field == 'country_id') {
                    $new = $getCountryName($new);
                    $old = $getCountryName($old);
                }

                $diff[$field] = [
                    'new' => $new,
                    'old' => $old,
                ];
            }
        }

        return view('lunarpanel::partials.orders.activity.address', [
            'log' => $log,
            'diff' => $diff,
            'type' => $type,
        ]);
    }
}
