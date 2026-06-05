<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.n8n.api_token');

        if (empty($token)) {
            abort(500, 'API token no configurado.');
        }

        $provided = $request->bearerToken()
            ?? $request->header('X-Api-Token')
            ?? $request->header('X-N8N-Webhook-Secret');

        if (! hash_equals($token, (string) $provided)) {
            abort(401, 'Token de API inválido.');
        }

        return $next($request);
    }
}
