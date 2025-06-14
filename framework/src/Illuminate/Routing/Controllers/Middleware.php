<?php

namespace Illuminate\Routing\Controllers;

use Closure;
use Illuminate\Support\Arr;

class Middleware
{
    /**
     * Create a new controller middleware definition.
     */
    public function __construct(public Closure|string|array $middleware, public ?array $only = null, public ?array $except = null) {}

    /**
     * Specify the only controller methods the middleware should apply to.
     *
     * @return $this
     */
    public function only(array|string $only)
    {
        $this->only = Arr::wrap($only);

        return $this;
    }

    /**
     * Specify the controller methods the middleware should not apply to.
     *
     * @return $this
     */
    public function except(array|string $except)
    {
        $this->except = Arr::wrap($except);

        return $this;
    }
}
