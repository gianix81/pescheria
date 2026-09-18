<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Autorizzazione lato server per gruppi di rotte: `->middleware('role:TECNICO')`. */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowed = array_map(fn (string $r) => Role::from($r), $roles);

        abort_unless($user->hasRole(...$allowed), 403, 'Non hai i permessi per accedere a questa sezione.');

        return $next($request);
    }
}
