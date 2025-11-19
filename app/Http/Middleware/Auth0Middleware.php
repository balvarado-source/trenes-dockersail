<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Cache;

class Auth0Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token no proporcionado'], 401);
        }

        try {
            $decoded = $this->validateToken($token);

            // Bloquear tokens de client-credentials (no hay usuario)
            $sub = $decoded->sub ?? '';
            if (str_ends_with($sub, '@clients') || (($decoded->gty ?? null) === 'client-credentials')) {
                throw new \Exception('Token de client-credentials no contiene usuario');
            }

            $user = $this->syncUser($decoded);
            $request->setUserResolver(fn () => $user);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
                'error' => config('app.debug') ? $e->getMessage() : 'unauthorized'
            ], 401);
        }
    }

    private function validateToken(string $token)
    {
        $domain = rtrim(env('AUTH0_DOMAIN', ''), '/');
        $audience = env('AUTH0_AUDIENCE');

        if (!$domain || !$audience) {
            throw new \Exception('Config Auth0 incompleta');
        }

        $jwks = Cache::remember('auth0_jwks', 300, function () use ($domain) {
            $jwksUrl = "https://{$domain}/.well-known/jwks.json";
            $response = @file_get_contents($jwksUrl);
            if ($response === false) {
                throw new \Exception("No se pudo obtener JWKS desde {$jwksUrl}");
            }
            $json = json_decode($response, true);
            if (!is_array($json) || !isset($json['keys'])) {
                throw new \Exception('JWKS inválido');
            }
            return $json;
        });

        try {
            $decoded = JWT::decode($token, JWK::parseKeySet($jwks, 'RS256'));
        } catch (\UnexpectedValueException $e) {
            // Si rota la clave y no existe el kid, limpiar caché y reintentar
            if (str_contains($e->getMessage(), 'kid')) {
                Cache::forget('auth0_jwks');
                $jwks = $this->fetchJwks($domain);
                $decoded = JWT::decode($token, JWK::parseKeySet($jwks, 'RS256'));
            } else {
                throw $e;
            }
        }

        $aud = is_array($decoded->aud ?? null) ? $decoded->aud : [($decoded->aud ?? '')];
        if (!in_array($audience, $aud, true)) {
            throw new \Exception('Audience inválido');
        }

        $expectedIss = "https://{$domain}/";
        if (($decoded->iss ?? '') !== $expectedIss) {
            throw new \Exception('Issuer inválido');
        }

        return $decoded;
    }

    private function fetchJwks(string $domain): array
    {
        $jwksUrl = "https://{$domain}/.well-known/jwks.json";
        $response = @file_get_contents($jwksUrl);
        if ($response === false) {
            throw new \Exception("No se pudo obtener JWKS desde {$jwksUrl}");
        }
        $json = json_decode($response, true);
        if (!is_array($json) || !isset($json['keys'])) {
            throw new \Exception('JWKS inválido');
        }
        Cache::put('auth0_jwks', $json, 300);
        return $json;
    }

    private function syncUser($auth0User)
    {
        $auth0Id = $auth0User->sub ?? null;
        if (!$auth0Id) {
            throw new \Exception('sub no presente en el token');
        }

        $namespace = 'https://hypotactic-nononerous-anette.ngrok-free.dev';
        
        // Intentar leer desde claims estándar o personalizados
        $email = $auth0User->email 
            ?? ($auth0User->{"{$namespace}/email"} ?? null);
        $name = $auth0User->name 
            ?? ($auth0User->{"{$namespace}/name"} ?? ($auth0User->nickname ?? 'Usuario'));
        $picture = $auth0User->picture 
            ?? ($auth0User->{"{$namespace}/picture"} ?? null);
        $verified = (bool)($auth0User->email_verified 
            ?? ($auth0User->{"{$namespace}/email_verified"} ?? false));

        if (!$email) {
            throw new \Exception('Email no presente en el token. Verifica la Action de Auth0.');
        }

        $user = \App\Models\User::updateOrCreate(
            ['auth0_id' => $auth0Id],
            [
                'name' => $name,
                'email' => $email,
                'avatar' => $picture,
                'email_verified_at' => $verified ? now() : null,
                'password' => bcrypt(str()->random(32)),
            ]
        );

        return $user;
    }
}
