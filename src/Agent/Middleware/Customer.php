<?php

namespace Lambda\Agent\Middleware;

use Closure;

class Customer
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config()->set('auth.providers.users.model', \Lambda\Agent\Models\Customer::class);
        return $next($request);
    }
}
