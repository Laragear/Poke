<?php

namespace Laragear\Poke\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use function csrf_field;
use function strpos;
use function substr_replace;

class InjectScript
{
    /**
     * Create a new middleware instance.
     *
     * @param  string  $mode
     */
    public function __construct(protected string $mode)
    {
        //
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $force
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $force = null): mixed
    {
        $response = $next($request);

        if ($this->shouldInject($request, $response, $force === 'force')) {
            $this->injectScript($response);
        }

        return $response;
    }

    /**
     * Determine if we should inject the script into the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @param  bool  $force
     * @return bool
     */
    public function shouldInject(Request $request, mixed $response, bool $force): bool
    {
        if (
            $this->mode !== 'blade' &&
            $response instanceof Response &&
            $response->isSuccessful() &&
            $request->acceptsHtml()
        ) {
            // The "auto" mode means to globally check if this is injectable.
            if ($this->mode === 'auto') {
                return $this->hasCsrfInput($response);
            }

            // Otherwise, the mode is "middleware": signal a forceful injection or CSRF input.
            return $force || $this->hasCsrfInput($response);
        }

        return false;
    }

    /**
     * Detect if the Response has form or CSRF Token.
     *
     * @param  \Illuminate\Http\Response  $response
     * @return bool
     */
    protected function hasCsrfInput(Response $response): bool
    {
        return strpos($response->content(), csrf_field());
    }

    /**
     * Sets the Script in the body
     *
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    protected function injectScript(Response $response): void
    {
        $endBodyPosition = strpos($content = $response->content(), '</body>');

        // To inject the script automatically, we will do it before the ending
        // body tag. If it's not found, the response may not be valid HTML,
        // so we will bail out returning the original untouched content.
        if ($endBodyPosition) {
            $response->setContent(
                substr_replace($content, Blade::render('<x-laragear.poke-script :force="true" />'), $endBodyPosition, 0)
            );
        }
    }
}