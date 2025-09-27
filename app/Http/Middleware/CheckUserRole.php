<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // El cambio clave está aquí: se añade "...$roles"
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // El primer argumento del middleware es el request,
        // los argumentos subsiguientes se pasarán a $roles
        if (!$user || !in_array($user->role_id, $roles)) {
            // Si el usuario no tiene el rol, lo redirecciona o muestra un error 403
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
