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
            // Solo redirigir en peticiones GET
            // Para peticiones PATCH/POST/DELETE, devolver 403
            if ($request->isMethod('GET')) {
                // Permitir acceso a las rutas de selección de rol
                if (! $request->routeIs('user.createRole') && ! $request->routeIs('user.storeRole')) {
                    return redirect()->route('user.createRole');
                }
            } else {
                // Para peticiones no-GET, abortar con 403
                abort(403, 'Debes seleccionar un rol antes de realizar esta acción');
            }
        }

        return $next($request);
    }
}
