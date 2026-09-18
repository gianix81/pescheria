<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** In produzione le credenziali demo devono essere cambiate al primo accesso. */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->must_change_password && ! $request->routeIs('password.change*', 'logout')) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
