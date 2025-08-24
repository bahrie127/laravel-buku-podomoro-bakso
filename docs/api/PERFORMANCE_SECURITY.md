# Performance & Security Guide

## Table of Contents

1. [Performance Optimization](#performance-optimization)
2. [Security Best Practices](#security-best-practices)
3. [Rate Limiting](#rate-limiting)
4. [Caching Strategies](#caching-strategies)
5. [Database Optimization](#database-optimization)
6. [Monitoring & Logging](#monitoring--logging)

## Performance Optimization

### Response Time Optimization

#### 1. Database Query Optimization

```php
// app/Http/Controllers/Api/AccountController.php

public function index(Request $request)
{
    $query = Account::query()
        ->where('user_id', auth()->id())
        ->select(['id', 'name', 'type', 'starting_balance', 'is_active', 'created_at']) // Select only needed columns
        ->orderBy('created_at', 'desc');

    // Efficient filtering
    if ($request->filled('q')) {
        $query->where('name', 'LIKE', '%' . $request->q . '%');
    }

    if ($request->filled('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
    }

    // Use pagination to limit results
    $accounts = $query->paginate(15);

    return response()->json([
        'message' => 'Accounts retrieved successfully',
        'data' => $accounts->items(),
        'pagination' => [
            'current_page' => $accounts->currentPage(),
            'per_page' => $accounts->perPage(),
            'total' => $accounts->total(),
            'last_page' => $accounts->lastPage(),
        ]
    ]);
}
```

#### 2. Eager Loading (untuk future endpoints dengan relationships)

```php
// When adding transaction endpoints
public function index()
{
    $accounts = Account::with(['transactions' => function ($query) {
        $query->latest()->limit(5); // Only recent transactions
    }])
    ->where('user_id', auth()->id())
    ->get();

    return response()->json([
        'message' => 'Accounts with transactions retrieved',
        'data' => $accounts
    ]);
}
```

#### 3. API Resource Optimization

```php
// app/Http/Resources/AccountResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'balance' => $this->when(
                $request->has('include_balance'),
                $this->balance
            ),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),

            // Conditional loading
            'transactions' => TransactionResource::collection(
                $this->whenLoaded('transactions')
            ),
        ];
    }
}
```

### Memory Optimization

#### 1. Chunk Large Data Processing

```php
// For bulk operations (future feature)
public function bulkUpdate(Request $request)
{
    Account::where('user_id', auth()->id())
        ->chunk(100, function ($accounts) use ($request) {
            foreach ($accounts as $account) {
                // Process each account
                $account->update($request->validated());
            }
        });

    return response()->json(['message' => 'Bulk update completed']);
}
```

#### 2. Streaming Responses untuk Large Data

```php
// For exporting large datasets
public function export()
{
    $callback = function () {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['ID', 'Name', 'Type', 'Balance', 'Active']);

        Account::where('user_id', auth()->id())
            ->chunk(1000, function ($accounts) use ($file) {
                foreach ($accounts as $account) {
                    fputcsv($file, [
                        $account->id,
                        $account->name,
                        $account->type,
                        $account->balance,
                        $account->is_active ? 'Yes' : 'No'
                    ]);
                }
            });

        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="accounts.csv"',
    ]);
}
```

## Security Best Practices

### 1. Input Validation & Sanitization

```php
// app/Http/Requests/StoreAccountRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-_]+$/'],
            'type' => ['required', 'string', 'in:cash,bank,credit_card,investment'],
            'starting_balance' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Account name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'type.in' => 'Account type must be one of: cash, bank, credit_card, investment.',
            'starting_balance.max' => 'Starting balance cannot exceed 999,999,999.99.',
        ];
    }

    protected function passedValidation()
    {
        // Sanitize input
        $this->merge([
            'name' => strip_tags(trim($this->name)),
            'description' => strip_tags(trim($this->description)),
        ]);
    }
}
```

### 2. SQL Injection Prevention

```php
// Always use parameterized queries
class AccountController extends Controller
{
    public function search(Request $request)
    {
        // GOOD: Using query builder with parameters
        $accounts = Account::where('user_id', auth()->id())
            ->where('name', 'LIKE', '%' . $request->input('q') . '%')
            ->get();

        // NEVER do this:
        // $accounts = DB::select("SELECT * FROM accounts WHERE name LIKE '%{$request->q}%'");

        return response()->json(['data' => $accounts]);
    }
}
```

### 3. Authentication & Authorization

```php
// app/Http/Middleware/ApiAuthMiddleware.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('sanctum')->check()) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        // Log authentication for security monitoring
        Log::info('API Access', [
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ]);

        return $next($request);
    }
}
```

### 4. CORS Configuration

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',    // React dev server
        'http://localhost:8080',    // Vue dev server
        'https://yourdomain.com',   // Production frontend
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### 5. Sensitive Data Protection

```php
// app/Models/User.php
class User extends Authenticatable
{
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at', // Hide verification details
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Never expose sensitive data in API responses
    public function toArray()
    {
        $array = parent::toArray();

        // Remove sensitive fields in API context
        if (request()->is('api/*')) {
            unset($array['email_verified_at']);
            unset($array['updated_at']);
        }

        return $array;
    }
}
```

## Rate Limiting

### 1. Custom Rate Limiting

```php
// app/Http/Middleware/ApiRateLimitMiddleware.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string $key = 'api')
    {
        $identifier = $this->resolveRequestIdentifier($request, $key);

        $limits = $this->getLimits($key);

        if (RateLimiter::tooManyAttempts($identifier, $limits['maxAttempts'])) {
            return $this->buildException($identifier, $limits['maxAttempts']);
        }

        RateLimiter::hit($identifier, $limits['decayMinutes'] * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $limits['maxAttempts'],
            RateLimiter::retriesLeft($identifier, $limits['maxAttempts'])
        );
    }

    protected function resolveRequestIdentifier(Request $request, string $key): string
    {
        if (auth()->check()) {
            return $key . ':' . auth()->id();
        }

        return $key . ':' . $request->ip();
    }

    protected function getLimits(string $key): array
    {
        $limits = [
            'auth' => ['maxAttempts' => 5, 'decayMinutes' => 1],      // 5 per minute for auth
            'api' => ['maxAttempts' => 60, 'decayMinutes' => 1],      // 60 per minute for general API
            'upload' => ['maxAttempts' => 10, 'decayMinutes' => 1],   // 10 per minute for uploads
        ];

        return $limits[$key] ?? $limits['api'];
    }

    protected function buildException(string $key, int $maxAttempts)
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'message' => 'Too many requests',
            'retry_after' => $retryAfter
        ], 429)->header('Retry-After', $retryAfter);
    }

    protected function addHeaders($response, int $maxAttempts, int $retriesLeft)
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $retriesLeft,
        ]);
    }
}
```

### 2. Route-Specific Rate Limiting

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:api-rate-limit'])->group(function () {
    Route::apiResource('accounts', AccountController::class);
});

Route::middleware(['throttle:auth-rate-limit'])->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth-rate-limit' => \App\Http\Middleware\ApiRateLimitMiddleware::class . ':auth',
        'api-rate-limit' => \App\Http\Middleware\ApiRateLimitMiddleware::class . ':api',
    ]);
})
```

## Caching Strategies

### 1. Response Caching

```php
// app/Http/Controllers/Api/AccountController.php

public function index(Request $request)
{
    $cacheKey = 'accounts:' . auth()->id() . ':' . md5($request->getQueryString());

    $accounts = Cache::remember($cacheKey, 300, function () use ($request) { // 5 minutes
        return Account::where('user_id', auth()->id())
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->q . '%');
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('created_at', 'desc')
            ->get();
    });

    return response()->json([
        'message' => 'Accounts retrieved successfully',
        'data' => $accounts
    ]);
}

public function store(StoreAccountRequest $request)
{
    $account = Account::create([
        'user_id' => auth()->id(),
        ...$request->validated()
    ]);

    // Clear user's account cache
    Cache::forget('accounts:' . auth()->id() . ':*');

    return response()->json([
        'message' => 'Account created successfully',
        'data' => $account
    ], 201);
}
```

### 2. Model Caching

```php
// app/Models/Account.php

class Account extends Model
{
    protected static function booted()
    {
        static::created(function ($account) {
            Cache::tags(['accounts', 'user:' . $account->user_id])->flush();
        });

        static::updated(function ($account) {
            Cache::tags(['accounts', 'user:' . $account->user_id])->flush();
        });

        static::deleted(function ($account) {
            Cache::tags(['accounts', 'user:' . $account->user_id])->flush();
        });
    }

    public function getBalanceAttribute()
    {
        return Cache::remember(
            "account_balance:{$this->id}",
            3600, // 1 hour
            function () {
                return $this->starting_balance + $this->transactions()->sum('amount');
            }
        );
    }
}
```

### 3. Redis Configuration untuk Production

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

// config/database.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

## Database Optimization

### 1. Database Indexes

```php
// database/migrations/add_indexes_to_accounts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Index for user accounts lookup
            $table->index(['user_id', 'is_active']);

            // Index for search functionality
            $table->index(['user_id', 'name']);

            // Composite index for common queries
            $table->index(['user_id', 'type', 'is_active']);

            // Index for sorting
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['user_id', 'name']);
            $table->dropIndex(['user_id', 'type', 'is_active']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
```

### 2. Query Optimization

```php
// Optimized queries with explain analyze
class AccountController extends Controller
{
    public function index(Request $request)
    {
        // Use query builder with proper indexing
        $query = Account::query()
            ->select(['id', 'name', 'type', 'starting_balance', 'is_active', 'created_at'])
            ->where('user_id', auth()->id());

        // Add filters efficiently
        if ($request->filled('q')) {
            $query->where('name', 'LIKE', $request->q . '%'); // Prefix search is faster
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Use consistent ordering with index
        $query->orderBy('created_at', 'desc');

        // Limit results to prevent memory issues
        $accounts = $query->limit(100)->get();

        return response()->json([
            'message' => 'Accounts retrieved successfully',
            'data' => $accounts
        ]);
    }
}
```

### 3. Database Connection Optimization

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true,  // Persistent connections
        PDO::ATTR_TIMEOUT => 30,       // Connection timeout
    ]) : [],
],
```

## Monitoring & Logging

### 1. API Performance Monitoring

```php
// app/Http/Middleware/ApiMonitoringMiddleware.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiMonitoringMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $this->logPerformance($request, $response, [
            'duration' => round(($endTime - $startTime) * 1000, 2), // milliseconds
            'memory_usage' => round(($endMemory - $startMemory) / 1024, 2), // KB
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2), // MB
        ]);

        return $response;
    }

    protected function logPerformance(Request $request, $response, array $metrics)
    {
        $logData = [
            'method' => $request->getMethod(),
            'url' => $request->getPathInfo(),
            'status_code' => $response->getStatusCode(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration_ms' => $metrics['duration'],
            'memory_kb' => $metrics['memory_usage'],
            'peak_memory_mb' => $metrics['peak_memory'],
            'query_count' => \DB::getQueryLog() ? count(\DB::getQueryLog()) : 0,
        ];

        // Log slow requests
        if ($metrics['duration'] > 1000) { // > 1 second
            Log::warning('Slow API Request', $logData);
        } else {
            Log::info('API Request', $logData);
        }

        // Log high memory usage
        if ($metrics['peak_memory'] > 50) { // > 50MB
            Log::warning('High Memory Usage', $logData);
        }
    }
}
```

### 2. Error Monitoring

```php
// app/Exceptions/Handler.php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (request()->is('api/*')) {
                Log::error('API Exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => request()->getPathInfo(),
                    'method' => request()->getMethod(),
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }
}
```

### 3. Health Check Endpoint

```php
// app/Http/Controllers/Api/HealthController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'services' => []
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['services']['database'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['database'] = 'unhealthy';
            $health['status'] = 'unhealthy';
        }

        // Cache check
        try {
            Cache::put('health-check', 'ok', 60);
            $health['services']['cache'] = Cache::get('health-check') === 'ok' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            $health['services']['cache'] = 'unhealthy';
            $health['status'] = 'unhealthy';
        }

        // Memory check
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $health['memory_usage_mb'] = round($memoryUsage, 2);

        if ($memoryUsage > 100) { // 100MB threshold
            $health['status'] = 'degraded';
        }

        return response()->json($health, $health['status'] === 'healthy' ? 200 : 503);
    }
}

// routes/api.php
Route::get('/health', [HealthController::class, 'check'])->name('api.health');
```

### 4. Metrics Collection

```php
// app/Services/MetricsService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    public function recordApiCall(string $endpoint, int $statusCode, float $duration)
    {
        $date = now()->format('Y-m-d');
        $hour = now()->format('H');

        // Increment daily API calls
        Cache::increment("metrics:api_calls:{$date}");

        // Increment hourly API calls
        Cache::increment("metrics:api_calls:{$date}:{$hour}");

        // Track status codes
        Cache::increment("metrics:status_codes:{$statusCode}:{$date}");

        // Track slow requests
        if ($duration > 1000) {
            Cache::increment("metrics:slow_requests:{$date}");
        }

        // Track endpoint usage
        Cache::increment("metrics:endpoints:" . str_replace('/', '_', $endpoint) . ":{$date}");
    }

    public function getMetrics(string $date = null): array
    {
        $date = $date ?: now()->format('Y-m-d');

        return [
            'date' => $date,
            'total_api_calls' => Cache::get("metrics:api_calls:{$date}", 0),
            'slow_requests' => Cache::get("metrics:slow_requests:{$date}", 0),
            'status_codes' => [
                '200' => Cache::get("metrics:status_codes:200:{$date}", 0),
                '201' => Cache::get("metrics:status_codes:201:{$date}", 0),
                '401' => Cache::get("metrics:status_codes:401:{$date}", 0),
                '404' => Cache::get("metrics:status_codes:404:{$date}", 0),
                '422' => Cache::get("metrics:status_codes:422:{$date}", 0),
                '500' => Cache::get("metrics:status_codes:500:{$date}", 0),
            ],
            'active_users' => DB::table('personal_access_tokens')
                ->whereDate('last_used_at', $date)
                ->distinct('tokenable_id')
                ->count(),
        ];
    }
}
```

## Production Deployment Checklist

### Environment Configuration

```bash
# .env for production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=bukubisnis_prod
DB_USERNAME=bukubisnis_user
DB_PASSWORD=secure_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=redis_password

# Sessions
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Rate limiting
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

### Server Configuration

```nginx
# nginx configuration
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com;
    root /var/www/bukubisnis/public;

    # SSL Configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/private.key;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self'" always;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;

    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Dengan dokumentasi performance dan security ini, API BukuBisnis akan siap untuk production dengan performa optimal dan keamanan yang kuat.
