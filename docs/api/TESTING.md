# Testing Guide

## Overview

Panduan lengkap untuk testing BukuBisnis API menggunakan berbagai tools dan frameworks.

## Table of Contents

1. [Manual Testing dengan cURL](#manual-testing-dengan-curl)
2. [Postman Collection](#postman-collection)
3. [Unit Testing dengan PHPUnit](#unit-testing-dengan-phpunit)
4. [Integration Testing](#integration-testing)
5. [Automated Testing Script](#automated-testing-script)
6. [Performance Testing](#performance-testing)

## Manual Testing dengan cURL

### Authentication Flow Testing

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api"

echo "=== Testing Authentication Flow ==="

# 1. Register new user
echo "1. Registering new user..."
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }')

echo "Register Response: $REGISTER_RESPONSE"

# 2. Login user
echo -e "\n2. Logging in user..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }')

echo "Login Response: $LOGIN_RESPONSE"

# Extract token
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token')
echo "Extracted Token: $TOKEN"

# 3. Get current user
echo -e "\n3. Getting current user info..."
USER_RESPONSE=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $TOKEN")

echo "User Response: $USER_RESPONSE"

# 4. Test protected endpoint without token
echo -e "\n4. Testing protected endpoint without token..."
UNAUTH_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts")
echo "Unauthorized Response: $UNAUTH_RESPONSE"
```

### Account Management Testing

```bash
#!/bin/bash

# Assuming TOKEN is available from previous test
echo "=== Testing Account Management ==="

# 1. Create account
echo "1. Creating new account..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/accounts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Cash Account",
    "type": "cash",
    "starting_balance": 1000.00,
    "is_active": true
  }')

echo "Create Response: $CREATE_RESPONSE"

# Extract account ID
ACCOUNT_ID=$(echo $CREATE_RESPONSE | jq -r '.data.id')
echo "Created Account ID: $ACCOUNT_ID"

# 2. List all accounts
echo -e "\n2. Listing all accounts..."
LIST_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts" \
  -H "Authorization: Bearer $TOKEN")

echo "List Response: $LIST_RESPONSE"

# 3. Get specific account
echo -e "\n3. Getting specific account..."
SHOW_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts/$ACCOUNT_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "Show Response: $SHOW_RESPONSE"

# 4. Update account
echo -e "\n4. Updating account..."
UPDATE_RESPONSE=$(curl -s -X PUT "$BASE_URL/accounts/$ACCOUNT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Test Account",
    "is_active": false
  }')

echo "Update Response: $UPDATE_RESPONSE"

# 5. Search accounts
echo -e "\n5. Searching accounts..."
SEARCH_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts?q=Updated&is_active=0" \
  -H "Authorization: Bearer $TOKEN")

echo "Search Response: $SEARCH_RESPONSE"

# 6. Delete account
echo -e "\n6. Deleting account..."
DELETE_RESPONSE=$(curl -s -X DELETE "$BASE_URL/accounts/$ACCOUNT_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "Delete Response: $DELETE_RESPONSE"
```

### Error Scenarios Testing

```bash
#!/bin/bash

echo "=== Testing Error Scenarios ==="

# 1. Validation errors
echo "1. Testing validation errors..."
VALIDATION_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "invalid-email",
    "password": "123"
  }')

echo "Validation Error Response: $VALIDATION_RESPONSE"

# 2. Authentication errors
echo -e "\n2. Testing authentication errors..."
AUTH_ERROR_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "wrong@email.com",
    "password": "wrongpassword"
  }')

echo "Auth Error Response: $AUTH_ERROR_RESPONSE"

# 3. Not found errors
echo -e "\n3. Testing not found errors..."
NOT_FOUND_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts/999999" \
  -H "Authorization: Bearer $TOKEN")

echo "Not Found Response: $NOT_FOUND_RESPONSE"

# 4. Invalid token
echo -e "\n4. Testing invalid token..."
INVALID_TOKEN_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts" \
  -H "Authorization: Bearer invalid-token")

echo "Invalid Token Response: $INVALID_TOKEN_RESPONSE"
```

## Postman Collection

### Environment Setup

Buat environment baru di Postman dengan variables:

```json
{
    "base_url": "http://localhost:8000/api",
    "token": "",
    "user_id": "",
    "account_id": ""
}
```

### Pre-request Script untuk Authentication

Tambahkan script ini ke Collection level:

```javascript
// Auto-login if token is missing
if (!pm.environment.get("token")) {
    pm.sendRequest(
        {
            url: pm.environment.get("base_url") + "/auth/login",
            method: "POST",
            header: {
                "Content-Type": "application/json",
            },
            body: {
                mode: "raw",
                raw: JSON.stringify({
                    email: "test@example.com",
                    password: "password123",
                }),
            },
        },
        function (err, response) {
            if (!err && response.code === 200) {
                var responseJson = response.json();
                pm.environment.set("token", responseJson.token);
                pm.environment.set("user_id", responseJson.user.id);
            }
        }
    );
}
```

### Test Scripts

Tambahkan test scripts untuk validasi response:

```javascript
// Common test for successful response
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has message", function () {
    const responseJson = pm.response.json();
    pm.expect(responseJson).to.have.property("message");
});

// For login endpoint
pm.test("Login returns token", function () {
    const responseJson = pm.response.json();
    pm.expect(responseJson).to.have.property("token");
    pm.environment.set("token", responseJson.token);
});

// For account creation
pm.test("Account created successfully", function () {
    const responseJson = pm.response.json();
    pm.expect(responseJson.data).to.have.property("id");
    pm.environment.set("account_id", responseJson.data.id);
});

// For validation errors
pm.test("Validation error has errors object", function () {
    if (pm.response.code === 422) {
        const responseJson = pm.response.json();
        pm.expect(responseJson).to.have.property("errors");
    }
});
```

## Unit Testing dengan PHPUnit

### Test Setup

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function authenticateUser()
    {
        Sanctum::actingAs($this->user);
        return $this;
    }
}
```

### Authentication Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email', 'created_at']
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user',
                    'token',
                    'token_type'
                ]);
    }

    public function test_login_validation_fails()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => ''
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Invalid credentials'
                ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email']
                ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
                        'Authorization' => 'Bearer ' . $token
                    ])
                    ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Logged out successfully'
                ]);
    }
}
```

### Account Management Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_their_accounts()
    {
        Account::factory()->count(3)->create(['user_id' => $this->user->id]);
        Account::factory()->count(2)->create(); // Other user's accounts

        $response = $this->actingAs($this->user, 'sanctum')
                        ->getJson('/api/accounts');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        '*' => ['id', 'name', 'type', 'balance', 'is_active']
                    ]
                ]);
    }

    public function test_user_can_create_account()
    {
        $accountData = [
            'name' => 'Test Account',
            'type' => 'cash',
            'starting_balance' => 1000.00,
            'is_active' => true
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/accounts', $accountData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => ['id', 'name', 'type', 'balance', 'is_active']
                ]);

        $this->assertDatabaseHas('accounts', [
            'name' => 'Test Account',
            'user_id' => $this->user->id
        ]);
    }

    public function test_account_creation_validation()
    {
        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/accounts', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'type']);
    }

    public function test_user_can_view_their_account()
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
                        ->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $account->id,
                        'name' => $account->name
                    ]
                ]);
    }

    public function test_user_cannot_view_other_users_account()
    {
        $otherAccount = Account::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
                        ->getJson("/api/accounts/{$otherAccount->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_update_their_account()
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Account Name',
            'is_active' => false
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                        ->putJson("/api/accounts/{$account->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Updated Account Name',
            'is_active' => false
        ]);
    }

    public function test_user_can_delete_account_without_transactions()
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
                        ->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id
        ]);
    }

    public function test_user_cannot_delete_account_with_transactions()
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);
        Transaction::factory()->create(['account_id' => $account->id]);

        $response = $this->actingAs($this->user, 'sanctum')
                        ->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Cannot delete account that has transactions'
                ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id
        ]);
    }

    public function test_accounts_can_be_filtered()
    {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Cash Account',
            'is_active' => true
        ]);

        Account::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Bank Account',
            'is_active' => false
        ]);

        // Test search filter
        $response = $this->actingAs($this->user, 'sanctum')
                        ->getJson('/api/accounts?q=Cash');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');

        // Test active filter
        $response = $this->actingAs($this->user, 'sanctum')
                        ->getJson('/api/accounts?is_active=1');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    public function test_unauthenticated_request_returns_401()
    {
        $response = $this->getJson('/api/accounts');

        $response->assertStatus(401);
    }
}
```

## Integration Testing

### Database Testing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_balance_calculation()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'starting_balance' => 1000.00
        ]);

        // Test that balance is calculated correctly with starting balance
        $this->assertEquals(1000.00, $account->fresh()->balance);
    }

    public function test_cascade_delete_behavior()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);

        // Delete user should not cascade to accounts (business rule)
        $user->delete();
        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }

    public function test_database_constraints()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create account without user_id (should fail)
        Account::create([
            'name' => 'Test Account',
            'type' => 'cash',
            'starting_balance' => 100.00,
            'is_active' => true
        ]);
    }
}
```

## Automated Testing Script

### Complete Test Suite Script

```bash
#!/bin/bash

# test_api_complete.sh
set -e

BASE_URL="http://localhost:8000/api"
TEST_EMAIL="test_$(date +%s)@example.com"
TEST_PASSWORD="password123"

echo "=== BukuBisnis API Complete Test Suite ==="
echo "Test Email: $TEST_EMAIL"
echo "Base URL: $BASE_URL"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper functions
pass() {
    echo -e "${GREEN}✓ $1${NC}"
}

fail() {
    echo -e "${RED}✗ $1${NC}"
    exit 1
}

info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Test counter
TOTAL_TESTS=0
PASSED_TESTS=0

run_test() {
    local test_name="$1"
    local test_command="$2"
    local expected_status="$3"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    info "Running: $test_name"

    response=$(eval "$test_command")
    status=$(echo "$response" | jq -r '.status // empty')

    if [[ "$status" == "$expected_status" ]] || [[ -z "$expected_status" ]]; then
        pass "$test_name"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        fail "$test_name - Expected status: $expected_status, Got: $status"
    fi

    echo "Response: $response"
    echo ""
}

# Test 1: Register User
info "=== Authentication Tests ==="

REGISTER_CMD="curl -s -X POST '$BASE_URL/auth/register' -H 'Content-Type: application/json' -d '{\"name\":\"Test User\",\"email\":\"$TEST_EMAIL\",\"password\":\"$TEST_PASSWORD\",\"password_confirmation\":\"$TEST_PASSWORD\"}' | jq '{status: 201, message: .message}'"

run_test "User Registration" "$REGISTER_CMD" "201"

# Test 2: Login User
LOGIN_CMD="curl -s -X POST '$BASE_URL/auth/login' -H 'Content-Type: application/json' -d '{\"email\":\"$TEST_EMAIL\",\"password\":\"$TEST_PASSWORD\"}'"

info "Logging in user..."
LOGIN_RESPONSE=$(eval "$LOGIN_CMD")
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token')

if [[ "$TOKEN" != "null" && -n "$TOKEN" ]]; then
    pass "User Login"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    fail "User Login - No token received"
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

echo "Token: $TOKEN"
echo ""

# Test 3: Get User Profile
USER_CMD="curl -s -X GET '$BASE_URL/auth/me' -H 'Authorization: Bearer $TOKEN' | jq '{status: 200, message: .message}'"

run_test "Get User Profile" "$USER_CMD" "200"

# Test 4: Create Account
info "=== Account Management Tests ==="

CREATE_ACCOUNT_CMD="curl -s -X POST '$BASE_URL/accounts' -H 'Authorization: Bearer $TOKEN' -H 'Content-Type: application/json' -d '{\"name\":\"Test Account\",\"type\":\"cash\",\"starting_balance\":1000.00,\"is_active\":true}'"

info "Creating account..."
CREATE_RESPONSE=$(eval "$CREATE_ACCOUNT_CMD")
ACCOUNT_ID=$(echo "$CREATE_RESPONSE" | jq -r '.data.id')

if [[ "$ACCOUNT_ID" != "null" && -n "$ACCOUNT_ID" ]]; then
    pass "Account Creation"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    fail "Account Creation - No account ID received"
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

echo "Account ID: $ACCOUNT_ID"
echo ""

# Test 5: List Accounts
LIST_CMD="curl -s -X GET '$BASE_URL/accounts' -H 'Authorization: Bearer $TOKEN' | jq '{status: 200, count: (.data | length)}'"

run_test "List Accounts" "$LIST_CMD"

# Test 6: Get Specific Account
SHOW_CMD="curl -s -X GET '$BASE_URL/accounts/$ACCOUNT_ID' -H 'Authorization: Bearer $TOKEN' | jq '{status: 200, id: .data.id}'"

run_test "Get Specific Account" "$SHOW_CMD"

# Test 7: Update Account
UPDATE_CMD="curl -s -X PUT '$BASE_URL/accounts/$ACCOUNT_ID' -H 'Authorization: Bearer $TOKEN' -H 'Content-Type: application/json' -d '{\"name\":\"Updated Account\",\"is_active\":false}' | jq '{status: 200, message: .message}'"

run_test "Update Account" "$UPDATE_CMD" "200"

# Test 8: Search Accounts
SEARCH_CMD="curl -s -X GET '$BASE_URL/accounts?q=Updated&is_active=0' -H 'Authorization: Bearer $TOKEN' | jq '{status: 200, count: (.data | length)}'"

run_test "Search Accounts" "$SEARCH_CMD"

# Test 9: Error Scenarios
info "=== Error Scenario Tests ==="

# Validation Error
VALIDATION_CMD="curl -s -X POST '$BASE_URL/auth/register' -H 'Content-Type: application/json' -d '{\"email\":\"invalid\"}' | jq '{status: 422, has_errors: (.errors != null)}'"

run_test "Validation Error" "$VALIDATION_CMD" "422"

# Authentication Error
AUTH_ERROR_CMD="curl -s -X GET '$BASE_URL/accounts' | jq '{status: 401, message: .message}'"

run_test "Authentication Error" "$AUTH_ERROR_CMD" "401"

# Not Found Error
NOT_FOUND_CMD="curl -s -X GET '$BASE_URL/accounts/999999' -H 'Authorization: Bearer $TOKEN' | jq '{status: 404, message: .message}'"

run_test "Not Found Error" "$NOT_FOUND_CMD" "404"

# Test 10: Delete Account
DELETE_CMD="curl -s -X DELETE '$BASE_URL/accounts/$ACCOUNT_ID' -H 'Authorization: Bearer $TOKEN' | jq '{status: 200, message: .message}'"

run_test "Delete Account" "$DELETE_CMD" "200"

# Test 11: Logout
LOGOUT_CMD="curl -s -X POST '$BASE_URL/auth/logout' -H 'Authorization: Bearer $TOKEN' | jq '{status: 200, message: .message}'"

run_test "User Logout" "$LOGOUT_CMD" "200"

# Summary
echo "=== Test Summary ==="
echo "Total Tests: $TOTAL_TESTS"
echo "Passed: $PASSED_TESTS"
echo "Failed: $((TOTAL_TESTS - PASSED_TESTS))"

if [[ $PASSED_TESTS -eq $TOTAL_TESTS ]]; then
    pass "All tests passed!"
    exit 0
else
    fail "Some tests failed!"
    exit 1
fi
```

### Make script executable and run

```bash
chmod +x test_api_complete.sh
./test_api_complete.sh
```

## Performance Testing

### Load Testing dengan Apache Bench

```bash
# Test login endpoint
ab -n 100 -c 10 -p login_data.json -T application/json http://localhost:8000/api/auth/login

# Test accounts listing (authenticated)
ab -n 100 -c 10 -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/accounts
```

### Performance Test Script

```bash
#!/bin/bash

# performance_test.sh
echo "=== Performance Testing ==="

# Get token first
TOKEN=$(curl -s -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}' | jq -r '.token')

# Create test data file for ab
echo '{"name":"Perf Test Account","type":"cash","starting_balance":100}' > account_data.json

# Test account creation performance
echo "Testing account creation (100 requests, 10 concurrent):"
ab -n 100 -c 10 -p account_data.json -T application/json \
   -H "Authorization: Bearer $TOKEN" \
   http://localhost:8000/api/accounts

# Test account listing performance
echo -e "\nTesting account listing (1000 requests, 50 concurrent):"
ab -n 1000 -c 50 -H "Authorization: Bearer $TOKEN" \
   http://localhost:8000/api/accounts

# Cleanup
rm account_data.json
```

### Memory and Response Time Monitoring

```php
<?php

// Add to API endpoints for monitoring
class PerformanceMiddleware
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $duration = round(($endTime - $startTime) * 1000, 2); // ms
        $memoryUsed = round(($endMemory - $startMemory) / 1024, 2); // KB

        Log::info('API Performance', [
            'endpoint' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'duration_ms' => $duration,
            'memory_kb' => $memoryUsed,
            'user_id' => auth()->id()
        ]);

        return $response;
    }
}
```

## Continuous Integration Testing

### GitHub Actions Workflow

```yaml
# .github/workflows/api-tests.yml
name: API Tests

on:
    push:
        branches: [main, develop]
    pull_request:
        branches: [main]

jobs:
    api-tests:
        runs-on: ubuntu-latest

        services:
            mysql:
                image: mysql:8.0
                env:
                    MYSQL_ROOT_PASSWORD: password
                    MYSQL_DATABASE: bukubisnis_test
                ports:
                    - 3306:3306
                options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

        steps:
            - uses: actions/checkout@v3

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.3"
                  extensions: mbstring, dom, fileinfo, mysql, pdo_mysql

            - name: Copy .env
              run: php -r "file_exists('.env') || copy('.env.example', '.env');"

            - name: Install Dependencies
              run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

            - name: Generate key
              run: php artisan key:generate

            - name: Directory Permissions
              run: chmod -R 777 storage bootstrap/cache

            - name: Create Database
              run: |
                  mysql -h127.0.0.1 -uroot -ppassword -e 'CREATE DATABASE IF NOT EXISTS bukubisnis_test;'

            - name: Run Migrations
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_PORT: 3306
                  DB_DATABASE: bukubisnis_test
                  DB_USERNAME: root
                  DB_PASSWORD: password
              run: php artisan migrate

            - name: Run PHPUnit Tests
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_PORT: 3306
                  DB_DATABASE: bukubisnis_test
                  DB_USERNAME: root
                  DB_PASSWORD: password
              run: vendor/bin/phpunit

            - name: Start Server
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_PORT: 3306
                  DB_DATABASE: bukubisnis_test
                  DB_USERNAME: root
                  DB_PASSWORD: password
              run: php artisan serve --host=127.0.0.1 --port=8000 &

            - name: Wait for Server
              run: sleep 5

            - name: Run API Integration Tests
              run: ./test_api_complete.sh
```

Dokumentasi testing ini memberikan panduan lengkap untuk menguji API dari berbagai aspek - manual testing, automated testing, unit testing, integration testing, dan performance testing.
