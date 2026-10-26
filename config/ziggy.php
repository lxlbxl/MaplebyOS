<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ziggy URL
    |--------------------------------------------------------------------------
    |
    | The base URL to use when generating Ziggy routes.
    |
    */
    'url' => env('APP_URL', 'https://adgod.d20.com.ng'),

    /*
    |--------------------------------------------------------------------------
    | Skip Route Function
    |--------------------------------------------------------------------------
    |
    | Exclude routes from Ziggy based on their properties.
    |
    */
    'skip-route-function' => null,

    /*
    |--------------------------------------------------------------------------
    | Groups
    |--------------------------------------------------------------------------
    |
    | If you want to define different route groups for different parts of your
    | application, you can do so here.
    |
    */
    'groups' => [],

    /*
    |--------------------------------------------------------------------------
    | Except
    |--------------------------------------------------------------------------
    |
    | Exclude specific routes from the bundled Ziggy object.
    |
    */
    'except' => [],

    /*
    |--------------------------------------------------------------------------
    | Only
    |--------------------------------------------------------------------------
    |
    | Include only specific routes in the Ziggy object.
    |
    */
    'only' => [],
];
