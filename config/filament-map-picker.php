<?php

use SalemAljebaly\FilamentMapPicker\Enums\ControlPosition;
use SalemAljebaly\FilamentMapPicker\Enums\TileProvider;

return [
    'default_location' => [
        'lat' => 30.0444,
        'lng' => 31.2357,
    ],

    'default_zoom' => 6,

    'height' => 400,

    'tile_provider' => TileProvider::OpenStreetMap->value,

    'auto_dark_mode' => true,

    'controls' => [
        'my_location' => true,
        'fullscreen' => true,
        'reset' => true,
    ],

    'control_position' => ControlPosition::BottomRight->value,

    'marker' => [
        'color' => '#059669',
        'draggable' => true,
    ],

    'search' => [
        'enabled' => true,
        'collapsible' => false,
        'min_length' => 3,
        'limit' => 5,
        'throttle_ms' => 1100,
        'nominatim' => [
            'search_url' => 'https://nominatim.openstreetmap.org/search',
            'reverse_url' => 'https://nominatim.openstreetmap.org/reverse',
            'email' => env('NOMINATIM_EMAIL'),
        ],
    ],
];
