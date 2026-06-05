<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiReadToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.api.read_token') ?: config('services.n8n.api_token');

        if (empty($token)) {
            abort(500, 'API read token no configurado.');
        }

        $provided = $request->bearerToken()
            ?? $request->header('X-Api-Token');

        if (! hash_equals($token, (string) $provided)) {
            abort(401, 'Token de API inválido.');
        }

        return $next($request);
    }
}
