# Cambios Requeridos en el Backend Laravel para Auth0

## 📋 Resumen

Para integrar Auth0 con tu backend Laravel, necesitas validar los tokens JWT que envía el frontend. Este documento detalla los cambios necesarios.

---

## 🎯 Opción Recomendada: Middleware Auth0

### Paso 1: Instalar Dependencias

```bash
cd tu-proyecto-laravel
composer require firebase/php-jwt
```

### Paso 2: Crear el Middleware

Crea el archivo `app/Http/Middleware/Auth0Middleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Cache;

class Auth0Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener el token del header Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación no proporcionado'
            ], 401);
        }

        try {
            // Validar y decodificar el token
            $decoded = $this->validateToken($token);
            
            // Sincronizar usuario con la base de datos
            $user = $this->syncUser($decoded);
            
            // Agregar el usuario al request para usarlo en los controladores
            $request->merge([
                'auth0_user' => $decoded,
                'user' => $user
            ]);
            
            // Autenticar al usuario en Laravel
            auth()->login($user);

            return $next($request);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Validar el token JWT de Auth0
     */
    private function validateToken(string $token)
    {
        $domain = env('AUTH0_DOMAIN');
        $audience = env('AUTH0_AUDIENCE');

        if (!$domain || !$audience) {
            throw new \Exception('Configuración de Auth0 incompleta');
        }

        // Obtener las claves públicas de Auth0 (con cache)
        $jwks = Cache::remember('auth0_jwks', 3600, function () use ($domain) {
            $jwksUrl = "https://{$domain}/.well-known/jwks.json";
            $response = file_get_contents($jwksUrl);
            return json_decode($response, true);
        });

        // Decodificar y validar el token
        $decoded = JWT::decode($token, JWK::parseKeySet($jwks));

        // Validar el audience
        $tokenAudience = is_array($decoded->aud) ? $decoded->aud : [$decoded->aud];
        if (!in_array($audience, $tokenAudience)) {
            throw new \Exception('Audience inválido');
        }

        // Validar el issuer
        $expectedIssuer = "https://{$domain}/";
        if ($decoded->iss !== $expectedIssuer) {
            throw new \Exception('Issuer inválido');
        }

        return $decoded;
    }

    /**
     * Sincronizar usuario de Auth0 con la base de datos local
     */
    private function syncUser($auth0User)
    {
        // Extraer información del token
        $email = $auth0User->email ?? null;
        $name = $auth0User->name ?? $auth0User->nickname ?? 'Usuario';
        $emailVerified = $auth0User->email_verified ?? false;
        $auth0Id = $auth0User->sub ?? null;

        if (!$email) {
            throw new \Exception('Email no encontrado en el token');
        }

        // Buscar o crear el usuario
        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email_verified_at' => $emailVerified ? now() : null,
                'password' => bcrypt(str()->random(32)), // Password aleatorio (no se usa)
            ]
        );

        // Actualizar información si cambió
        if ($user->name !== $name) {
            $user->update(['name' => $name]);
        }

        if ($emailVerified && !$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        return $user;
    }
}
```

### Paso 3: Registrar el Middleware

En `app/Http/Kernel.php`, agrega el middleware a `$routeMiddleware`:

```php
protected $routeMiddleware = [
    // ... otros middlewares
    'auth0' => \App\Http\Middleware\Auth0Middleware::class,
];
```

### Paso 4: Configurar Variables de Entorno

En el archivo `.env` de Laravel:

```env
# Auth0 Configuration
AUTH0_DOMAIN=dev-abc123.us.auth0.com
AUTH0_AUDIENCE=https://blog-api
```

**Importante**: Estos valores deben coincidir con los configurados en Auth0.

### Paso 5: Actualizar las Rutas

En `routes/api.php`:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

// Rutas públicas (sin autenticación)
Route::get('/hello', function () {
    return response()->json(['message' => 'API funcionando correctamente']);
});

// Rutas protegidas con Auth0
Route::middleware('auth0')->group(function () {
    
    // Obtener usuario autenticado
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    
    // Recursos protegidos
    Route::apiResource('posts', PostController::class);
    Route::apiResource('comments', CommentController::class);
    
    // Logout (opcional, solo limpia sesión local)
    Route::get('/logout', function (Request $request) {
        auth()->logout();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    });
});
```

### Paso 6: Actualizar los Controladores (Opcional)

Si necesitas acceder al usuario autenticado en tus controladores:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request)
    {
        // El usuario ya está autenticado por el middleware
        $user = $request->user();
        
        $posts = Post::with('user')->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Crear post asociado al usuario autenticado
        $post = $request->user()->posts()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post creado correctamente'
        ], 201);
    }
}
```

---

## 🔄 Opción Alternativa: Sistema Híbrido

Si quieres mantener el sistema de autenticación actual (Sanctum/Passport) y agregar Auth0:

### Crear Middleware Dual

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DualAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Intentar autenticación con Sanctum primero
        if (auth('sanctum')->check()) {
            return $next($request);
        }

        // Si no, intentar con Auth0
        $auth0Middleware = new Auth0Middleware();
        return $auth0Middleware->handle($request, $next);
    }
}
```

Registrar en `Kernel.php`:

```php
'dual-auth' => \App\Http\Middleware\DualAuthMiddleware::class,
```

Usar en rutas:

```php
Route::middleware('dual-auth')->group(function () {
    // Rutas que aceptan ambos tipos de autenticación
});
```

---

## 🗄️ Migración de Base de Datos (Opcional)

Si quieres almacenar el ID de Auth0:

```bash
php artisan make:migration add_auth0_id_to_users_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth0_id')->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('auth0_id');
        });
    }
};
```

Actualizar el método `syncUser` en el middleware:

```php
private function syncUser($auth0User)
{
    $auth0Id = $auth0User->sub;
    $email = $auth0User->email;
    
    $user = \App\Models\User::firstOrCreate(
        ['auth0_id' => $auth0Id],
        [
            'email' => $email,
            'name' => $auth0User->name ?? 'Usuario',
            'email_verified_at' => $auth0User->email_verified ? now() : null,
            'password' => bcrypt(str()->random(32)),
        ]
    );
    
    return $user;
}
```

---

## 🧪 Testing

### Probar el Middleware

```bash
# Obtener un token desde el frontend
# Luego hacer una petición con curl

curl -X GET http://laravel.test/api/user \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

### Respuesta Esperada

```json
{
  "id": 1,
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "email_verified_at": "2024-01-15T10:30:00.000000Z",
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

---

## 🔒 Seguridad

### Buenas Prácticas

1. **Cache de JWKS**: El middleware ya implementa cache para las claves públicas
2. **Validación de Audience**: Siempre valida el audience del token
3. **Validación de Issuer**: Verifica que el token viene de tu tenant de Auth0
4. **HTTPS**: En producción, siempre usa HTTPS
5. **Rate Limiting**: Implementa rate limiting en las rutas de API

### Rate Limiting (Opcional)

En `routes/api.php`:

```php
Route::middleware(['auth0', 'throttle:60,1'])->group(function () {
    // Máximo 60 requests por minuto
});
```

---

## 🐛 Troubleshooting

### Error: "Class 'Firebase\JWT\JWT' not found"

```bash
composer require firebase/php-jwt
composer dump-autoload
```

### Error: "file_get_contents(): SSL operation failed"

Problema con certificados SSL. Solución:

```php
// En el método validateToken, usar:
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);
$response = file_get_contents($jwksUrl, false, $context);
```

**⚠️ Solo para desarrollo. En producción, soluciona los certificados SSL.**

### Error: "Audience inválido"

Verifica que `AUTH0_AUDIENCE` en Laravel coincida exactamente con el API Identifier en Auth0.

---

## 📚 Recursos

- [Firebase JWT PHP](https://github.com/firebase/php-jwt)
- [Auth0 Laravel Documentation](https://auth0.com/docs/quickstart/backend/php)
- [JWT.io Debugger](https://jwt.io/)

---

## ✅ Checklist de Implementación

- [ ] Instalar `firebase/php-jwt`
- [ ] Crear `Auth0Middleware.php`
- [ ] Registrar middleware en `Kernel.php`
- [ ] Configurar variables `.env`
- [ ] Actualizar rutas en `api.php`
- [ ] Probar con token real
- [ ] Verificar sincronización de usuarios
- [ ] Implementar manejo de errores
- [ ] Configurar cache de JWKS
- [ ] Testing en desarrollo
- [ ] Testing en producción

---

## 🎯 Resultado Final

Después de implementar estos cambios:

1. ✅ El frontend envía el token de Auth0 en cada petición
2. ✅ El backend valida el token automáticamente
3. ✅ Los usuarios se sincronizan con la base de datos
4. ✅ Las rutas protegidas solo son accesibles con token válido
5. ✅ El usuario autenticado está disponible en `$request->user()`
