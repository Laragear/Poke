<?php

namespace Laragear\Poke\Http\Controllers;

use Illuminate\Http\Response;

class PokeController
{
    /**
     * Return an empty Ok response to the Poke script.
     */
    public function __invoke(): Response
    {
        return new Response(status: 204);
    }
}
