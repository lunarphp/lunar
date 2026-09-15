<?php

return [
    'selection' => [
        'malformed' => 'The bundle selection is malformed.',
        'unknown_group' => 'The bundle has no option group ":id".',
        'unknown_component' => 'Option ":id" is not available in ":group".',
        'duplicate_component' => 'An option in ":group" was chosen more than once.',
        'count' => 'Choose between :min and :max options in ":group".',
        'unavailable' => 'Bundle component ":identifier" is not available at this quantity.',
    ],
    'nesting' => [
        'is_component' => 'A variant that is part of another bundle cannot be a bundle itself.',
        'is_bundle' => 'A bundle cannot contain another bundle.',
        'self' => 'A bundle cannot contain its own variant.',
    ],
    'definition' => [
        'empty' => 'A bundle needs at least one component.',
        'too_many_components' => 'A bundle cannot have more than :max components.',
        'unknown_group' => 'The option group does not belong to this bundle.',
        'group_selections' => 'The selection limits for ":group" must satisfy minimum <= maximum <= number of options.',
    ],
    'pricing' => [
        'missing_component_price' => 'Component ":identifier" has no price in :currency, so the bundle cannot be priced in that currency.',
    ],
    'console' => [
        'repriced' => 'Repriced :count bundles.',
    ],
];
