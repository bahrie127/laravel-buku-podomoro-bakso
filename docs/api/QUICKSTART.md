# Quick Start Guide - BukuBisnis API

## 🚀 Getting Started

### Prerequisites

-   Laravel development server running
-   Database migrated
-   Laravel Sanctum installed and configured

### 1. Start the Server

```bash
php artisan serve
```

Server will run at: `http://localhost:8000`

### 2. Test API Endpoints

#### Authentication Flow

```bash
# 1. Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Login to get token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Save the token from response
export TOKEN="1|your-token-here"

# 3. Get current user info
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

#### Account Management

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
```

## 📁 Using Postman

### Import Collection

1. Open Postman
2. Click "Import"
3. Upload `docs/api/BukuBisnis-API.postman_collection.json`
4. Upload `docs/api/BukuBisnis.postman_environment.json`

### Test Flow

1. Select "BukuBisnis Environment"
2. Run "Register User" request
3. Run "Login User" request (token will be saved automatically)
4. Run other requests (they will use the saved token)

## 🧪 Automated Testing

### Run Test Script

```bash
# Make script executable
chmod +x test_account_api.sh

# Run tests
./test_account_api.sh
```

## 📊 API Endpoints Overview

| Method    | Endpoint         | Auth Required | Description                  |
| --------- | ---------------- | ------------- | ---------------------------- |
| POST      | `/auth/register` | ❌            | Register new user            |
| POST      | `/auth/login`    | ❌            | Login user                   |
| GET       | `/auth/me`       | ✅            | Get current user             |
| POST      | `/auth/logout`   | ✅            | Logout user                  |
| GET       | `/accounts`      | ✅            | List accounts with filtering |
| POST      | `/accounts`      | ✅            | Create new account           |
| GET       | `/accounts/{id}` | ✅            | Show account details         |
| PUT/PATCH | `/accounts/{id}` | ✅            | Update account               |
| DELETE    | `/accounts/{id}` | ✅            | Delete account               |

## 🔧 Account Types

-   `cash` - Kas/Tunai
-   `bank` - Rekening Bank
-   `ewallet` - E-Wallet (OVO, GoPay, dll)
-   `other` - Lainnya

## ⚠️ Important Notes

1. **Authentication**: Semua endpoint kecuali register/login memerlukan Bearer token
2. **User Isolation**: User hanya bisa akses data milik mereka sendiri
3. **Account Deletion**: Akun yang memiliki transaksi tidak bisa dihapus
4. **Token Management**: Login baru akan revoke token lama

## 🐛 Troubleshooting

### Token Issues

-   Pastikan token disertakan dalam header: `Authorization: Bearer {token}`
-   Token expires setelah logout atau login ulang
-   Cek response login untuk mendapatkan token baru

### 404 Errors

-   Pastikan ID resource benar
-   Pastikan resource milik user yang sedang login
-   Cek apakah endpoint path benar

### Validation Errors (422)

-   Cek required fields
-   Pastikan format data sesuai (email, numeric, dll)
-   Lihat response error untuk detail validasi

## 📞 Support

Untuk bantuan lebih lanjut:

-   Cek dokumentasi lengkap di `docs/api/README.md`
-   Review kode controller di `app/Http/Controllers/Api/`
-   Cek routes di `routes/api.php`
