# Error Handling Guide

## Overview

BukuBisnis API menggunakan standar HTTP status codes dan format response yang konsisten untuk semua error handling.

## Error Response Format

### Standard Error Structure

```json
{
    "message": "Error description",
    "errors": {
        "field": ["Specific field error message"]
    }
}
```

### Validation Error Structure

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required.",
            "The email must be a valid email address."
        ],
        "password": ["The password field is required."]
    }
}
```

## HTTP Status Codes

### Success Codes

| Code | Status     | Description                             |
| ---- | ---------- | --------------------------------------- |
| 200  | OK         | Request successful                      |
| 201  | Created    | Resource created successfully           |
| 204  | No Content | Request successful, no content returned |

### Client Error Codes

| Code | Status               | Description                        |
| ---- | -------------------- | ---------------------------------- |
| 400  | Bad Request          | Invalid request format             |
| 401  | Unauthorized         | Authentication required or invalid |
| 403  | Forbidden            | Access denied                      |
| 404  | Not Found            | Resource not found                 |
| 422  | Unprocessable Entity | Validation errors                  |
| 429  | Too Many Requests    | Rate limit exceeded                |

### Server Error Codes

| Code | Status                | Description                     |
| ---- | --------------------- | ------------------------------- |
| 500  | Internal Server Error | Server error                    |
| 503  | Service Unavailable   | Service temporarily unavailable |

## Common Error Scenarios

### 1. Authentication Errors (401)

**Missing Token:**

```json
{
    "message": "Unauthenticated."
}
```

**Invalid Token:**

```json
{
    "message": "Unauthenticated."
}
```

**Expired Token:**

```json
{
    "message": "Token has expired"
}
```

### 2. Validation Errors (422)

**Registration Validation:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "email": [
            "The email field is required.",
            "The email must be a valid email address.",
            "The email has already been taken."
        ],
        "password": [
            "The password must be at least 8 characters.",
            "The password confirmation does not match."
        ]
    }
}
```

**Account Creation Validation:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required.",
            "The name must not be greater than 255 characters."
        ],
        "type": ["The selected type is invalid."],
        "starting_balance": [
            "The starting balance must be a number.",
            "The starting balance must be at least 0."
        ]
    }
}
```

### 3. Resource Not Found (404)

**Account Not Found:**

```json
{
    "message": "Account not found"
}
```

**Route Not Found:**

```json
{
    "message": "The route could not be found."
}
```

### 4. Business Logic Errors

**Cannot Delete Account with Transactions:**

```json
{
    "message": "Cannot delete account that has transactions"
}
```

**Login Failed:**

```json
{
    "message": "Invalid credentials",
    "errors": {
        "email": ["These credentials do not match our records."]
    }
}
```

### 5. Rate Limiting (429)

```json
{
    "message": "Too Many Attempts."
}
```

## Error Handling Best Practices

### Client-Side Implementation

```javascript
class ApiErrorHandler {
    static async handleResponse(response) {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new ApiError(response.status, errorData);
        }
        return response.json();
    }

    static handleError(error) {
        if (error instanceof ApiError) {
            switch (error.status) {
                case 401:
                    // Redirect to login
                    window.location.href = "/login";
                    break;
                case 422:
                    // Show validation errors
                    this.showValidationErrors(error.data.errors);
                    break;
                case 404:
                    // Show not found message
                    this.showNotification("Resource not found", "error");
                    break;
                case 500:
                    // Show generic error
                    this.showNotification("Server error occurred", "error");
                    break;
                default:
                    this.showNotification(
                        error.data.message || "An error occurred",
                        "error"
                    );
            }
        }
    }

    static showValidationErrors(errors) {
        Object.keys(errors).forEach((field) => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                const errorDiv =
                    input.parentNode.querySelector(".error-message");
                if (errorDiv) {
                    errorDiv.textContent = errors[field][0];
                    errorDiv.style.display = "block";
                }
            }
        });
    }

    static showNotification(message, type = "info") {
        // Implement your notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

class ApiError extends Error {
    constructor(status, data) {
        super(data.message || "API Error");
        this.status = status;
        this.data = data;
    }
}

// Usage example
async function createAccount(accountData) {
    try {
        const response = await fetch("/api/accounts", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify(accountData),
        });

        return await ApiErrorHandler.handleResponse(response);
    } catch (error) {
        ApiErrorHandler.handleError(error);
        throw error;
    }
}
```

### React Implementation

```jsx
import { useState } from "react";

function useApiError() {
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const clearErrors = () => setErrors({});

    const handleApiCall = async (apiCall) => {
        setLoading(true);
        setErrors({});

        try {
            const result = await apiCall();
            return result;
        } catch (error) {
            if (error.status === 422) {
                setErrors(error.data.errors || {});
            } else if (error.status === 401) {
                // Handle authentication error
                logout();
                navigate("/login");
            } else {
                // Handle other errors
                showNotification(error.data.message || "An error occurred");
            }
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return { errors, loading, clearErrors, handleApiCall };
}

// Component usage
function AccountForm() {
    const { errors, loading, handleApiCall } = useApiError();
    const [formData, setFormData] = useState({});

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            await handleApiCall(() => createAccount(formData));
            // Handle success
        } catch (error) {
            // Error already handled by useApiError
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <input
                name="name"
                value={formData.name || ""}
                onChange={(e) =>
                    setFormData({ ...formData, name: e.target.value })
                }
            />
            {errors.name && <div className="error">{errors.name[0]}</div>}

            <button type="submit" disabled={loading}>
                {loading ? "Creating..." : "Create Account"}
            </button>
        </form>
    );
}
```

### Vue.js Implementation

```vue
<template>
    <form @submit.prevent="handleSubmit">
        <input
            v-model="form.name"
            name="name"
            :class="{ error: errors.name }"
        />
        <div v-if="errors.name" class="error-message">
            {{ errors.name[0] }}
        </div>

        <button type="submit" :disabled="loading">
            {{ loading ? "Creating..." : "Create Account" }}
        </button>
    </form>
</template>

<script>
export default {
    data() {
        return {
            form: {
                name: "",
                type: "",
                starting_balance: 0,
            },
            errors: {},
            loading: false,
        };
    },

    methods: {
        async handleSubmit() {
            this.loading = true;
            this.errors = {};

            try {
                await this.createAccount(this.form);
                this.$emit("account-created");
            } catch (error) {
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.$toast.error(error.response.data.message);
                }
            } finally {
                this.loading = false;
            }
        },

        async createAccount(data) {
            const response = await this.$http.post("/api/accounts", data);
            return response.data;
        },
    },
};
</script>
```

## Server-Side Error Handling

### Custom Exception Handler

Untuk menangani error secara konsisten, Anda dapat membuat custom exception handler:

```php
// app/Exceptions/ApiException.php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected $statusCode;
    protected $errors;

    public function __construct(string $message, int $statusCode = 400, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        $response = ['message' => $this->getMessage()];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return response()->json($response, $this->statusCode);
    }
}
```

### Global Exception Handling

```php
// bootstrap/app.php
use App\Exceptions\ApiException;
use Illuminate\Http\Request;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (Throwable $e, Request $request) {
        if ($request->is('api/*')) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'message' => 'The requested resource was not found.'
                ], 404);
            }

            if ($e instanceof ApiException) {
                return $e->render();
            }

            // Generic server error
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    });
})
```

## Testing Error Scenarios

### cURL Examples

```bash
# Test validation error
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email": "invalid-email"}' \
  -v

# Test authentication error
curl -X GET http://localhost:8000/api/accounts \
  -H "Authorization: Bearer invalid-token" \
  -v

# Test not found error
curl -X GET http://localhost:8000/api/accounts/999999 \
  -H "Authorization: Bearer valid-token" \
  -v
```

### Unit Test Examples

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ErrorHandlingTest extends TestCase
{
    public function test_validation_error_returns_422()
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors' => [
                        'name',
                        'email',
                        'password'
                    ]
                ]);
    }

    public function test_unauthenticated_request_returns_401()
    {
        $response = $this->getJson('/api/accounts');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_not_found_account_returns_404()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->getJson('/api/accounts/999999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Account not found'
                ]);
    }
}
```

## Monitoring and Logging

### Error Logging

```php
// Log API errors for monitoring
Log::error('API Error', [
    'user_id' => auth()->id(),
    'endpoint' => $request->getPathInfo(),
    'method' => $request->getMethod(),
    'error' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString()
]);
```

### Error Metrics

Track common errors untuk monitoring:

-   Authentication failures
-   Validation errors by field
-   Rate limit hits
-   Server errors

Ini membantu mengidentifikasi patterns dan improve API reliability.
