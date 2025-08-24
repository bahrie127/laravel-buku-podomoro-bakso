# BukuBisnis API Documentation

## Overview

BukuBisnis API adalah RESTful API untuk aplikasi pembukuan ringan yang menyediakan autentikasi menggunakan Laravel Sanctum dan manajemen akun keuangan.

## Base URL

```
http://your-domain.com/api
```

## Authentication

API menggunakan Laravel Sanctum untuk autentikasi. Setelah login, Anda akan mendapatkan Bearer token yang harus disertakan dalam header Authorization untuk endpoint yang memerlukan autentikasi.

```
Authorization: Bearer {your-token}
```

## Response Format

Semua response menggunakan format JSON dengan struktur konsisten:

**Success Response:**

```json
{
    "message": "Success message",
    "data": { ... }
}
```

**Error Response:**

```json
{
    "message": "Error message",
    "errors": { ... }
}
```

---

# Authentication Endpoints

## 1. Register User

**POST** `/auth/register`

Mendaftarkan user baru ke sistem.

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Validation Rules:**

-   `name`: required, string, max 255 characters
-   `email`: required, email format, unique
-   `password`: required, min 8 characters, confirmed

**Response (201):**

```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-08-23T21:45:00.000000Z"
    }
}
```

**Error Response (422):**

```json
{
    "message": "Validation error",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password confirmation does not match."]
    }
}
```

---

## 2. Login User

**POST** `/auth/login`

Login user dan mendapatkan access token.

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**

```json
{
    "message": "Login successful",
    "token": "1|abc123def456...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

**Error Response (401):**

```json
{
    "message": "Invalid credentials"
}
```

---

## 3. Get Current User

**GET** `/auth/me`

🔒 **Requires Authentication**

Mendapatkan informasi user yang sedang login.

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2025-08-23T21:45:00.000000Z",
        "updated_at": "2025-08-23T21:45:00.000000Z"
    }
}
```

---

## 4. Logout User

**POST** `/auth/logout`

🔒 **Requires Authentication**

Logout user dan revoke semua access token.

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "message": "Logged out successfully"
}
```

---

# Account Management Endpoints

## 1. List Accounts

**GET** `/accounts`

🔒 **Requires Authentication**

Mendapatkan daftar akun milik user dengan opsi filtering dan pencarian.

**Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `q` (optional): Search by account name
-   `is_active` (optional): Filter by active status (1 for active, 0 for inactive)

**Example Request:**

```
GET /api/accounts?q=cash&is_active=1
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
        },
        {
            "id": 2,
            "user_id": 1,
            "name": "Bank BCA",
            "type": "bank",
            "starting_balance": 5000.0,
            "is_active": true,
            "created_at": "2025-08-23T21:46:00.000000Z",
            "updated_at": "2025-08-23T21:46:00.000000Z"
        }
    ],
    "meta": {
        "total": 2,
        "filters_applied": {
            "search": "cash",
            "is_active": "1"
        }
    }
}
```

---

## 2. Create Account

**POST** `/accounts`

🔒 **Requires Authentication**

Membuat akun baru untuk user yang sedang login.

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "name": "Bank Mandiri",
    "type": "bank",
    "starting_balance": 2500.0,
    "is_active": true
}
```

**Validation Rules:**

-   `name`: required, string, max 255 characters
-   `type`: required, must be one of: `cash`, `bank`, `ewallet`, `other`
-   `starting_balance`: required, numeric, minimum 0
-   `is_active`: optional, boolean (default: true)

**Response (201):**

```json
{
    "message": "Account created successfully",
    "data": {
        "id": 3,
        "user_id": 1,
        "name": "Bank Mandiri",
        "type": "bank",
        "starting_balance": 2500.0,
        "is_active": true,
        "created_at": "2025-08-23T21:47:00.000000Z",
        "updated_at": "2025-08-23T21:47:00.000000Z"
    }
}
```

**Error Response (422):**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["Account name is required"],
        "type": ["Account type must be one of: cash, bank, ewallet, other"],
        "starting_balance": ["Starting balance cannot be negative"]
    }
}
```

---

## 3. Show Account

**GET** `/accounts/{id}`

🔒 **Requires Authentication**

Mendapatkan detail akun spesifik (hanya akun milik user yang sedang login).

**Headers:**

```
Authorization: Bearer {token}
```

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

**Error Response (404):**

```json
{
    "message": "Account not found"
}
```

---

## 4. Update Account

**PUT/PATCH** `/accounts/{id}`

🔒 **Requires Authentication**

Update informasi akun (hanya akun milik user yang sedang login).

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (partial update allowed):**

```json
{
    "name": "Updated Account Name",
    "is_active": false
}
```

**Validation Rules (all optional for update):**

-   `name`: string, max 255 characters
-   `type`: must be one of: `cash`, `bank`, `ewallet`, `other`
-   `starting_balance`: numeric, minimum 0
-   `is_active`: boolean

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

**Error Response (404):**

```json
{
    "message": "Account not found"
}
```

---

## 5. Delete Account

**DELETE** `/accounts/{id}`

🔒 **Requires Authentication**

Hapus akun (hanya akun milik user yang sedang login dan tidak memiliki transaksi).

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "message": "Account deleted successfully"
}
```

**Error Response (422) - Account has transactions:**

```json
{
    "message": "Cannot delete account that has transactions"
}
```

**Error Response (404):**

```json
{
    "message": "Account not found"
}
```

---

# Error Codes

| HTTP Code | Description                                         |
| --------- | --------------------------------------------------- |
| 200       | Success                                             |
| 201       | Created                                             |
| 401       | Unauthorized - Invalid credentials or missing token |
| 404       | Not Found - Resource not found or not owned by user |
| 422       | Validation Error - Invalid input data               |
| 500       | Internal Server Error                               |

---

# Example Usage

## 1. Complete Authentication Flow

```bash
# 1. Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Save the token from response
TOKEN="1|abc123def456..."

# 3. Get current user
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

## 2. Account Management Flow

```bash
# 1. Create account
curl -X POST http://localhost:8000/api/accounts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Cash Wallet",
    "type": "cash",
    "starting_balance": 1000.00,
    "is_active": true
  }'

# 2. List all accounts
curl -X GET http://localhost:8000/api/accounts \
  -H "Authorization: Bearer $TOKEN"

# 3. Search accounts
curl -X GET "http://localhost:8000/api/accounts?q=cash&is_active=1" \
  -H "Authorization: Bearer $TOKEN"

# 4. Show specific account
curl -X GET http://localhost:8000/api/accounts/1 \
  -H "Authorization: Bearer $TOKEN"

# 5. Update account
curl -X PUT http://localhost:8000/api/accounts/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Cash Wallet",
    "is_active": false
  }'

# 6. Delete account
curl -X DELETE http://localhost:8000/api/accounts/1 \
  -H "Authorization: Bearer $TOKEN"
```

---

# Rate Limiting

API menggunakan Laravel default rate limiting:

-   **Authentication endpoints**: 60 requests per minute
-   **Protected endpoints**: 60 requests per minute per authenticated user

---

# Versioning

Current API version: **v1**

Future versions akan menggunakan URL versioning:

-   `/api/v1/...`
-   `/api/v2/...`

---

# Support

Untuk pertanyaan atau masalah terkait API, silakan hubungi tim development atau buat issue di repository project.
