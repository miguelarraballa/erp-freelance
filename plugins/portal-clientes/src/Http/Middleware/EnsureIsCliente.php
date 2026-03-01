<?php

namespace PortalClientes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCliente
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('cliente') || !$user->cliente()->exists()) {
            abort(403, 'Acceso restringido a clientes.');
        }

        return $next($request);
    }
}
