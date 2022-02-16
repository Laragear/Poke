<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode of injection
    |--------------------------------------------------------------------------
    |
    | Specify which injection to use in your application. By default, poking
    | will look into all your Responses for CSRF tokens and add the script
    | but you can change it to having more control on when to inject it.
    |
    | Supported: "auto", "middleware", "blade",
    |
    */

    'mode' => env('POKE_MODE', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Times
    |--------------------------------------------------------------------------
    |
    | You can set by how many times in the session lifetime the poking will be
    | made to your application. For example, the default 120 minutes session
    | lifetime, divided by 4 times, means poking at a 30 minutes intervals.
    |
    */

    'times' => 4,

    /*
    |--------------------------------------------------------------------------
    | Route for Poking
    |--------------------------------------------------------------------------
    |
    | Here you may specify how the poking route will live in your application.
    | You can set a specific route to be hit, a custom name to identify it,
    | and a custom subdomain if you don't want to be available app-wide.
    |
    | If the "route" is null or false, no route will be registered in the app,
    | allowing you to set your register your own Route with custom resolution
    | or domain, which may be useful if you're using subdomains or patterns.
    |
    */

    'poking' => [
        'route' => 'poke',
        'name' => 'poke',
        'domain' => null,
        'middleware' => ['web'],
    ],
];
