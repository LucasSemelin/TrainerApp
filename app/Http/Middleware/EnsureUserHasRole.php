<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si el usuario está autenticado y no tiene roles asignados
        if ($user && ! $user->roles()->exists()) {
            // Permitir acceso a las rutas de selección de rol
            if (! $request->routeIs('user.createRole') && ! $request->routeIs('user.storeRole')) {
                return redirect()->route('user.createRole');
            }
        }

        return $next($request);
    }
}
