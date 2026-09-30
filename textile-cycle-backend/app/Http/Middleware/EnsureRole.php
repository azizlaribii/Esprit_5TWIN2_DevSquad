<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage : ->middleware('role:association') ou ->middleware('role:user,admin')
 *
 * Alias à déclarer dans bootstrap/app.php (Laravel 11+) :
 *   $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $roles, true), 403, 'Accès réservé.');

        return $next($request);
    }
}
