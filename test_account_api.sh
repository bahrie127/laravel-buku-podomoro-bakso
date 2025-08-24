#!/bin/bash

# API Test Script untuk Account Controller
BASE_URL="http://127.0.0.1:8000/api"

echo "=== Testing Account API ==="

# 1. Register user untuk testing
echo "1. Registering test user..."
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }')
echo "Register Response: $REGISTER_RESPONSE"

# 2. Login untuk mendapatkan token
echo -e "\n2. Logging in..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }')
echo "Login Response: $LOGIN_RESPONSE"

# Extract token from response
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "Token: $TOKEN"

if [ -z "$TOKEN" ]; then
  echo "Failed to get token, exiting..."
  exit 1
fi

# 3. Create account
echo -e "\n3. Creating account..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/accounts" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Cash Wallet",
    "type": "cash",
    "starting_balance": 1000.00,
    "is_active": true
  }')
echo "Create Account Response: $CREATE_RESPONSE"

# 4. List accounts
echo -e "\n4. Listing accounts..."
LIST_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts" \
  -H "Authorization: Bearer $TOKEN")
echo "List Accounts Response: $LIST_RESPONSE"

# 5. Search accounts
echo -e "\n5. Searching accounts with query 'cash'..."
SEARCH_RESPONSE=$(curl -s -X GET "$BASE_URL/accounts?q=cash&is_active=1" \
  -H "Authorization: Bearer $TOKEN")
echo "Search Response: $SEARCH_RESPONSE"

echo -e "\n=== Account API Test Completed ==="
