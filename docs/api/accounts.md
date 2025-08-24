# Account API Documentation

## Base URL

```
/api/accounts
```

## Authentication

All endpoints require Bearer token authentication using Laravel Sanctum.

## Endpoints

### 1. GET /api/accounts - List Accounts

**Description:** Get all accounts for authenticated user with optional filtering

**Query Parameters:**

-   `q` (optional): Search by account name
-   `is_active` (optional): Filter by active status (1 for active, 0 for inactive)

**Example Request:**

```bash
GET /api/accounts?q=cash&is_active=1
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "name": "Cash Wallet",
            "type": "cash",
            "starting_balance": 1000.0,
            "is_active": true,
            "created_at": "2025-08-23T21:45:00.000000Z",
            "updated_at": "2025-08-23T21:45:00.000000Z"
        }
    ],
    "meta": {
        "total": 1,
        "filters_applied": {
            "search": "cash",
            "is_active": "1"
        }
    }
}
```

### 2. POST /api/accounts - Create Account

**Description:** Create a new account for authenticated user

**Request Body:**

```json
{
    "name": "Bank BCA",
    "type": "bank",
    "starting_balance": 5000.0,
    "is_active": true
}
```

**Validation Rules:**

-   `name`: required, string, max 255 characters
-   `type`: required, must be one of: cash, bank, ewallet, other
-   `starting_balance`: required, numeric, minimum 0
-   `is_active`: optional, boolean (default: true)

**Response (201):**

```json
{
    "message": "Account created successfully",
    "data": {
        "id": 2,
        "user_id": 1,
        "name": "Bank BCA",
        "type": "bank",
        "starting_balance": 5000.0,
        "is_active": true,
        "created_at": "2025-08-23T21:45:00.000000Z",
        "updated_at": "2025-08-23T21:45:00.000000Z"
    }
}
```

### 3. GET /api/accounts/{id} - Show Account

**Description:** Get specific account details (only if belongs to authenticated user)

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "name": "Cash Wallet",
        "type": "cash",
        "starting_balance": 1000.0,
        "is_active": true,
        "created_at": "2025-08-23T21:45:00.000000Z",
        "updated_at": "2025-08-23T21:45:00.000000Z"
    }
}
```

**Response (404):**

```json
{
    "message": "Account not found"
}
```

### 4. PUT/PATCH /api/accounts/{id} - Update Account

**Description:** Update account details (only if belongs to authenticated user)

**Request Body (partial update allowed):**

```json
{
    "name": "Updated Account Name",
    "is_active": false
}
```

**Response (200):**

```json
{
    "message": "Account updated successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "name": "Updated Account Name",
        "type": "cash",
        "starting_balance": 1000.0,
        "is_active": false,
        "created_at": "2025-08-23T21:45:00.000000Z",
        "updated_at": "2025-08-23T21:50:00.000000Z"
    }
}
```

### 5. DELETE /api/accounts/{id} - Delete Account

**Description:** Delete account (only if belongs to authenticated user and has no transactions)

**Response (200):**

```json
{
    "message": "Account deleted successfully"
}
```

**Response (422) - Has transactions:**

```json
{
    "message": "Cannot delete account that has transactions"
}
```

**Response (404):**

```json
{
    "message": "Account not found"
}
```

## Error Responses

### Validation Error (422):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["Account name is required"],
        "type": ["Account type must be one of: cash, bank, ewallet, other"]
    }
}
```

### Unauthorized (401):

```json
{
    "message": "Unauthenticated."
}
```
