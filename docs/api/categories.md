# Category Management API

## Overview

API untuk mengelola kategori income dan expense dengan dukungan hierarchical categories (parent-child relationships).

## Base URL

```
/api/categories
```

## Authentication

Semua endpoint memerlukan authentication menggunakan Bearer token dari Laravel Sanctum.

```
Authorization: Bearer {your-token}
```

## Endpoints

### 1. List Categories

**GET** `/api/categories`

Mengambil daftar kategori milik user yang sedang login.

**Query Parameters:**

-   `type` (optional): Filter berdasarkan type (`income` atau `expense`)

**Request Example:**

```bash
# Get all categories
curl -X GET "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get only income categories
curl -X GET "http://localhost:8000/api/categories?type=income" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get only expense categories
curl -X GET "http://localhost:8000/api/categories?type=expense" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200):**

```json
{
    "message": "Categories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Salary",
            "type": "income",
            "parent_id": null,
            "user_id": 1,
            "created_at": "2025-08-23T22:00:00.000000Z",
            "updated_at": "2025-08-23T22:00:00.000000Z",
            "parent": null
        },
        {
            "id": 2,
            "name": "Food & Dining",
            "type": "expense",
            "parent_id": null,
            "user_id": 1,
            "created_at": "2025-08-23T22:00:00.000000Z",
            "updated_at": "2025-08-23T22:00:00.000000Z",
            "parent": null
        },
        {
            "id": 3,
            "name": "Restaurant",
            "type": "expense",
            "parent_id": 2,
            "user_id": 1,
            "created_at": "2025-08-23T22:00:00.000000Z",
            "updated_at": "2025-08-23T22:00:00.000000Z",
            "parent": {
                "id": 2,
                "name": "Food & Dining",
                "type": "expense"
            }
        }
    ]
}
```

### 2. Create Category

**POST** `/api/categories`

Membuat kategori baru.

**Request Body:**

```json
{
    "name": "Transportation",
    "type": "expense",
    "parent_id": null
}
```

**Validation Rules:**

-   `name`: required, string, max 255 characters, unique per user per type
-   `type`: required, enum (`income`, `expense`)
-   `parent_id`: optional, must exist in user's categories with same type

**Request Example:**

```bash
# Create parent category
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Transportation",
    "type": "expense"
  }'

# Create child category
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Public Transport",
    "type": "expense",
    "parent_id": 4
  }'
```

**Response (201):**

```json
{
    "message": "Category created successfully",
    "data": {
        "id": 4,
        "name": "Transportation",
        "type": "expense",
        "parent_id": null,
        "user_id": 1,
        "created_at": "2025-08-23T22:05:00.000000Z",
        "updated_at": "2025-08-23T22:05:00.000000Z",
        "parent": null
    }
}
```

**Error Response (422) - Duplicate Name:**

```json
{
    "message": "Validation error",
    "errors": {
        "name": ["A category with this name and type already exists."]
    }
}
```

**Error Response (422) - Invalid Parent:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "parent_id": [
            "The selected parent category is invalid or does not belong to you."
        ]
    }
}
```

### 3. Show Category

**GET** `/api/categories/{id}`

Mengambil detail kategori beserta parent dan children.

**Request Example:**

```bash
curl -X GET "http://localhost:8000/api/categories/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200):**

```json
{
    "message": "Category retrieved successfully",
    "data": {
        "id": 2,
        "name": "Food & Dining",
        "type": "expense",
        "parent_id": null,
        "user_id": 1,
        "created_at": "2025-08-23T22:00:00.000000Z",
        "updated_at": "2025-08-23T22:00:00.000000Z",
        "parent": null,
        "children": [
            {
                "id": 3,
                "name": "Restaurant",
                "type": "expense",
                "parent_id": 2
            },
            {
                "id": 5,
                "name": "Groceries",
                "type": "expense",
                "parent_id": 2
            }
        ]
    }
}
```

**Error Response (404):**

```json
{
    "message": "Category not found"
}
```

### 4. Update Category

**PUT** `/api/categories/{id}`

Memperbarui kategori. Semua field bersifat optional (partial update).

**Request Body:**

```json
{
    "name": "Transportation Updated",
    "type": "expense",
    "parent_id": null
}
```

**Request Example:**

```bash
# Update name only
curl -X PUT "http://localhost:8000/api/categories/4" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Transportation Updated"
  }'

# Update parent
curl -X PUT "http://localhost:8000/api/categories/5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "parent_id": 4
  }'

# Remove parent (make it root category)
curl -X PUT "http://localhost:8000/api/categories/5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "parent_id": null
  }'
```

**Response (200):**

```json
{
    "message": "Category updated successfully",
    "data": {
        "id": 4,
        "name": "Transportation Updated",
        "type": "expense",
        "parent_id": null,
        "user_id": 1,
        "created_at": "2025-08-23T22:05:00.000000Z",
        "updated_at": "2025-08-23T22:10:00.000000Z",
        "parent": null,
        "children": []
    }
}
```

**Business Rule Validations:**

**Error Response (422) - Type Change with Children:**

```json
{
    "message": "Validation error",
    "errors": {
        "type": ["Cannot change type: category has children of different type."]
    }
}
```

**Error Response (422) - Type Change with Parent:**

```json
{
    "message": "Validation error",
    "errors": {
        "type": ["Cannot change type: parent category has different type."]
    }
}
```

**Error Response (422) - Circular Reference:**

```json
{
    "message": "Validation error",
    "errors": {
        "parent_id": ["Cannot set parent: would create circular reference."]
    }
}
```

**Error Response (422) - Self Reference:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "parent_id": ["A category cannot be its own parent."]
    }
}
```

### 5. Delete Category

**DELETE** `/api/categories/{id}`

Menghapus kategori. Kategori hanya bisa dihapus jika:

-   Tidak memiliki transaksi
-   Tidak memiliki sub-kategori (children)

**Request Example:**

```bash
curl -X DELETE "http://localhost:8000/api/categories/4" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200):**

```json
{
    "message": "Category deleted successfully"
}
```

**Error Response (422) - Has Transactions:**

```json
{
    "message": "Cannot delete category that has transactions"
}
```

**Error Response (422) - Has Children:**

```json
{
    "message": "Cannot delete category that has subcategories"
}
```

**Error Response (404):**

```json
{
    "message": "Category not found"
}
```

## Business Rules

### 1. Data Isolation

-   Users hanya bisa mengakses kategori milik mereka sendiri
-   Semua operasi dibatasi berdasarkan `user_id`

### 2. Uniqueness

-   Nama kategori harus unique per user per type
-   Constraint: `(user_id, name, type)` unique

### 3. Hierarchical Relationships

-   Parent dan child harus memiliki `type` yang sama
-   Tidak boleh ada circular reference
-   Kategori tidak boleh menjadi parent dari dirinya sendiri

### 4. Type Consistency

-   Jika mengubah type kategori, harus memastikan:
    -   Tidak ada children dengan type berbeda
    -   Parent (jika ada) memiliki type yang sama

### 5. Deletion Rules

-   Kategori dengan transaksi tidak bisa dihapus
-   Kategori dengan sub-kategori tidak bisa dihapus
-   Harus hapus children atau transaksi terlebih dahulu

## Error Handling

### Validation Errors (422)

-   Nama duplikat
-   Parent tidak valid
-   Type tidak sesuai
-   Circular reference
-   Business rule violations

### Authorization Errors (401)

-   Token tidak valid atau expired

### Not Found Errors (404)

-   Kategori tidak ditemukan
-   Kategori bukan milik user

### Server Errors (500)

-   Database errors
-   Unexpected exceptions

## Usage Examples

### Complete Category Management Flow

```bash
# 1. Login terlebih dahulu
TOKEN=$(curl -s -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.token')

# 2. Create income categories
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Employment","type":"income"}'

curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Salary","type":"income","parent_id":1}'

curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Bonus","type":"income","parent_id":1}'

# 3. Create expense categories
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Food & Dining","type":"expense"}'

curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Restaurant","type":"expense","parent_id":4}'

curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Groceries","type":"expense","parent_id":4}'

# 4. List all categories
curl -X GET "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN"

# 5. List only income categories
curl -X GET "http://localhost:8000/api/categories?type=income" \
  -H "Authorization: Bearer $TOKEN"

# 6. Get category with children
curl -X GET "http://localhost:8000/api/categories/4" \
  -H "Authorization: Bearer $TOKEN"

# 7. Update category name
curl -X PUT "http://localhost:8000/api/categories/5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Dining Out"}'

# 8. Move category to different parent
curl -X PUT "http://localhost:8000/api/categories/6" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"parent_id":null}'

# 9. Delete category (only if no transactions/children)
curl -X DELETE "http://localhost:8000/api/categories/6" \
  -H "Authorization: Bearer $TOKEN"
```

### Frontend Integration Example (JavaScript)

```javascript
class CategoryAPI {
    constructor(token) {
        this.token = token;
        this.baseURL = "http://localhost:8000/api";
    }

    async getCategories(type = null) {
        const url = type
            ? `${this.baseURL}/categories?type=${type}`
            : `${this.baseURL}/categories`;
        const response = await fetch(url, {
            headers: { Authorization: `Bearer ${this.token}` },
        });
        return response.json();
    }

    async createCategory(data) {
        const response = await fetch(`${this.baseURL}/categories`, {
            method: "POST",
            headers: {
                Authorization: `Bearer ${this.token}`,
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });
        return response.json();
    }

    async updateCategory(id, data) {
        const response = await fetch(`${this.baseURL}/categories/${id}`, {
            method: "PUT",
            headers: {
                Authorization: `Bearer ${this.token}`,
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });
        return response.json();
    }

    async deleteCategory(id) {
        const response = await fetch(`${this.baseURL}/categories/${id}`, {
            method: "DELETE",
            headers: { Authorization: `Bearer ${this.token}` },
        });
        return response.json();
    }

    // Build hierarchical tree from flat list
    buildCategoryTree(categories) {
        const tree = [];
        const map = {};

        categories.forEach((category) => {
            map[category.id] = { ...category, children: [] };
        });

        categories.forEach((category) => {
            if (category.parent_id) {
                map[category.parent_id].children.push(map[category.id]);
            } else {
                tree.push(map[category.id]);
            }
        });

        return tree;
    }
}

// Usage
const categoryAPI = new CategoryAPI("your-token-here");

// Get all categories and build tree
categoryAPI.getCategories().then((response) => {
    const tree = categoryAPI.buildCategoryTree(response.data);
    console.log("Category tree:", tree);
});

// Get only expense categories
categoryAPI.getCategories("expense").then((response) => {
    console.log("Expense categories:", response.data);
});

// Create new category
categoryAPI
    .createCategory({
        name: "Transportation",
        type: "expense",
    })
    .then((response) => {
        console.log("Created category:", response.data);
    });
```

## API Testing

Untuk menguji API categories, Anda bisa menggunakan collection Postman yang sudah disediakan atau script testing otomatis yang akan dibuat.

### Quick Test Script

```bash
#!/bin/bash

# Test Category API
echo "Testing Category Management API..."

# Login and get token
TOKEN=$(curl -s -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}' | jq -r '.token')

echo "Token: $TOKEN"

# Test create category
echo "Creating income category..."
INCOME_CAT=$(curl -s -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Employment","type":"income"}')

echo "Income category created: $INCOME_CAT"

# Test create expense category
echo "Creating expense category..."
EXPENSE_CAT=$(curl -s -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Food & Dining","type":"expense"}')

echo "Expense category created: $EXPENSE_CAT"

# Test list categories
echo "Listing all categories..."
ALL_CATS=$(curl -s -X GET "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer $TOKEN")

echo "All categories: $ALL_CATS"

# Test filter by type
echo "Listing income categories..."
INCOME_CATS=$(curl -s -X GET "http://localhost:8000/api/categories?type=income" \
  -H "Authorization: Bearer $TOKEN")

echo "Income categories: $INCOME_CATS"

echo "Category API testing completed!"
```

Category Management API sudah siap digunakan dengan fitur-fitur yang lengkap dan business rules yang ketat untuk menjaga integritas data!
