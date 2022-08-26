# Poke
[![Latest Version on Packagist](https://img.shields.io/packagist/v/laragear/poke.svg)](https://packagist.org/packages/laragear/poke)
[![Latest stable test run](https://github.com/Laragear/Poke/workflows/Tests/badge.svg)](https://github.com/Laragear/Poke/actions)
[![Codecov coverage](https://codecov.io/gh/Laragear/Poke/branch/1.x/graph/badge.svg?token=0ELJR5X90J)](https://codecov.io/gh/Laragear/Poke)
[![Maintainability](https://api.codeclimate.com/v1/badges/3954af3144475603ae67/maintainability)](https://codeclimate.com/github/Laragear/Poke/maintainability)
[![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=Laragear_Poke&metric=alert_status)](https://sonarcloud.io/dashboard?id=Laragear_Poke)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://laravel.com/docs/9.x/octane#introduction)

Keep your forms alive, avoid `TokenMismatchException` by gently poking your Laravel app.

## Become a sponsor

[![](.github/assets/support.png)](https://github.com/sponsors/DarkGhostHunter)

Your support allows me to keep this package free, up-to-date and maintainable. Alternatively, you can **[spread the word!](http://twitter.com/share?text=I%20am%20using%20this%20cool%20PHP%20package&url=https://github.com%2FLaragear%2FPoke&hashtags=PHP,Laravel)**

## Requirements

* PHP 8.0 or later.
* Laravel 9.x or later.

## Installation

Require this package into your project using Composer:

```bash
composer require laragear/poke
```

## How does it work?

This package pokes your App with an HTTP `HEAD` request to the `/poke` route at given intervals. In return, while your application renews the session lifetime, it returns an `HTTP 204` status code, which is an OK Response without body. 

This amounts to **barely 0.8 KB sent!**

### Automatic Reloading on CSRF token expiration

The Poke script will detect if the CSRF session token is expired based on the last successful poke, and forcefully reload the page if there is Internet connection.

This is done by detecting [when the browser or tab becomes active](https://developer.mozilla.org/en-US/docs/Web/API/Page_Visibility_API), or [when the device user becomes online again](https://developer.mozilla.org/en-US/docs/Web/API/NavigatorOnLine/onLine).

This is handy in situations when the user laptop is put to sleep, or the phone loses signal. Because the session may expire during these moments, the page is reloaded to get the new CSRF token when the browser wakes up or the phone becomes online.

## Usage

There are three ways to turn on Poke in your app. 

* `auto` (easy hands-off default)
* `middleware`
* `blade` (best performance)

You can change the default mode using your environment file:

```dotenv
POKE_MODE=auto
```

### `auto`

Just install this package and *look at it go*. This will append a middleware in [the `web` group](https://laravel.com/docs/middleware#middleware-groups) that will look into all your Responses content where:

* the request accepts HTML, and
* an input with `csrf` token is present.

If there is any match, this will inject the Poke script in charge to keep the forms alive just before the `</body>` tag.

This mode won't inject the script on error responses or redirections.

> It's recommended to use the other modes if your application has many routes or Responses with a lot of text.

### `middleware`

This mode does not push the middleware to the `web` group. Instead, it allows you to use the `poke` middleware only in the routes you decide.

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;

Route::get('register', RegisterController::class)->middleware('poke');
```

This will inject the script into the route response if there is an input with a CSRF token. You can also apply this to a [route group](https://laravel.com/docs/routing#route-groups).

You may want to use the `force` option to forcefully inject the script at the end of the `<body>` tag, regardless of the CSRF token input presence. This may be handy when you expect to dynamically load forms on a view after its loaded, or SPA.

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;

Route::get('status', StatusController::class)->middleware('poke:force');
```

As with [`auto` mode](#auto), this mode won't inject the script on errors or redirections.

### `blade`

The `blade` mode disables middleware injection, so you can use the `<x-poke-script />` component freely to inject the script anywhere in your view, preferably before the closing `</body>` tag.

```blade
<body>
    <h2>Try to Login:</h2>
    <form action="/login" method="post">
        @csrf
        <input type="text" name="username" required>
        <input type="password" name="password" required>
        <button type="submit">Log me in!</button>
    </form>
    
    <x-poke-script /> <!-- This is a good place to put it -->
</body>
```

This may be useful if you have large responses, like blog posts, articles or galleries, since the framework won't spend resources inspecting the response, but just rendering the component.

> Don't worry if you have duplicate Poke components in your view. The script is rendered only once, and even if not, the script only runs once.

## Configuration

For fine-tuning, you can publish the `poke.php` config file.

```bash
php artisan vendor:publish --provider="Laragear\Poke\PokeServiceProvider" --tag="config"
```

Let's examine the configuration array:

```php
return [
    'mode' => env('POKE_MODE', 'auto'),
    'times' => 4,
    'poking' => [
        'route' => 'poke',
        'name' => 'poke',
        'domain' => null,
        'middleware' => 'web',
    ]
];
```

### Times (Interval)

How many times the poking will be done relative to the global session lifetime. The more times, the shorter the poking interval. The default `4` should be fine for any normal application. 

For example, if our session lifetime is the default of 120 minutes:

- 3 times will poke the application each 40 minutes, 
- 4 times will poke the application each 30 minutes, 
- 5 times will poke the application each 24 minutes,
- 6 times will poke the application each 20 minutes, and so on...

In other words, `session lifetime / times = poking interval`.

- 🔺 Raise the intervals if you expect users idling in your site for several minutes, even hours.
- 🔻 Lower the intervals if you expect users with a lot of activity.

### Poking

This is the array of settings for the poking route which receives the Poke script request.

```php
return [
    'poking' => [
        'route' => 'poke',
        'name' => 'poke',
        'domain' => null,
        'middleware' => ['web'],
    ]
];
```

#### Route

The route (relative to the root URL of your application) that will be using to receive the pokes.

```php
return [
    'poking' => [
        'route' => '/dont-sleep'
    ],
];
```

> The poke routes are registered at boot time.

#### Name

Name of the route, to find the poke route in your app for whatever reason.

```php
return [
    'poking' => [
        'name' => 'my-custom-poking-route'
    ],
];
```

#### Domain

The Poke route is available on all domains. Setting a given domain will scope the route to that domain.

In case you are using a domain or domain pattern, it may be convenient to put the Poke route under a certain one. A classic example is to make the poking available at `http://user.myapp.com/poke` but no `http://api.myapp.com/poke`.

```php
return [
    'poking' => [
        'domain' => '{user}.myapp.com'
    ],
];
```

#### Middleware

The default Poke route uses [the `web` middleware group](https://laravel.com/docs/middleware#middleware-groups) to function properly, as this group handles session, cookies and CSRF tokens. 

You can add your own middleware here if you need to.

```php
return [
    'poking' => [
        'middleware' => ['web', 'validates-ip', 'my-custom-middleware']
    ],
];
```

You can also use the "bare minimum" middleware if you feel like it, thus it may be problematic if you don't know what you're doing.

```php
return [
    'poking' => [
        'middleware' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],
    ]
]
```

## Script View

Poke injects the script as a [Blade component](https://laravel.com/docs/blade#components) at all times.

You can override the script by publishing it under the `views` tag:

```bash
php artisan vendor:publish --provider="Laragear\Poke\PokeServiceProvider" --tag="views"
```

Some people may want to change the script to use a custom Javascript HTTP library, minify the response, make it compatible for older browsers, or even [create a custom Event](https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Creating_and_triggering_events) when CSRF token expires.

The view receives three variables:

* `$route`: The relative route where the poking will be done.
* `$interval`: The interval in milliseconds the poking should be done.
* `$lifetime`: The session lifetime in milliseconds.

## Laravel Octane compatibility

- Only the `InjectsScript` middleware is bound as a singleton, and it saves the mode, which will persist across all the process lifetime. The mode is not intended to change request-by-request.

There should be no problems using this package with Laravel Octane.

## Security

If you discover any security related issues, please email darkghosthunter@gmail.com instead of using the issue tracker.

# License

This specific package version is licensed under the terms of the [MIT License](LICENSE.md), at time of publishing.

[Laravel](https://laravel.com) is a Trademark of [Taylor Otwell](https://github.com/TaylorOtwell/). Copyright © 2011-2022 Laravel LLC.
