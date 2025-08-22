# Laravel Bookkeeping System - Database Design

## Overview

This document describes the database design for the Laravel Bookkeeping System, featuring a simple yet flexible multi-user accounting structure with support for accounts, categories, transactions, attachments, and recurring transactions.

## Entity Relationship Diagram (ERD)

```
users (Laravel default)
├── accounts (1:many)
├── categories (1:many)
├── transactions (1:many)
└── recurring_rules (1:many)

accounts
└── transactions (1:many)

categories
├── transactions (1:many)
├── categories (self-referential, parent-child)
└── recurring_rules (1:many)

transactions
├── attachments (1:many)
└── transfer_group_id (UUID for linking transfer pairs)

recurring_rules
└── generates transactions over time
```

## Tables

### 1. `users` (Laravel default)

Standard Laravel user authentication table.

### 2. `accounts`

Represents financial accounts (wallets, banks, e-wallets, etc.)

| Field            | Type          | Description                |
| ---------------- | ------------- | -------------------------- |
| id               | bigint        | Primary key                |
| user_id          | bigint        | Foreign key to users       |
| name             | varchar       | Account name               |
| type             | enum          | cash, bank, ewallet, other |
| starting_balance | decimal(15,2) | Initial account balance    |
| is_active        | boolean       | Account status             |
| created_at       | timestamp     |                            |
| updated_at       | timestamp     |                            |

**Indexes:**

-   `user_id, is_active`

### 3. `categories`

Income/expense categories with optional nested structure

| Field      | Type            | Description           |
| ---------- | --------------- | --------------------- |
| id         | bigint          | Primary key           |
| user_id    | bigint          | Foreign key to users  |
| name       | varchar         | Category name         |
| type       | enum            | income, expense       |
| parent_id  | bigint nullable | For nested categories |
| created_at | timestamp       |                       |
| updated_at | timestamp       |                       |

**Indexes:**

-   `user_id, type`
-   `parent_id`

### 4. `transactions`

All financial transactions (income, expense, transfers)

| Field             | Type             | Description                       |
| ----------------- | ---------------- | --------------------------------- |
| id                | bigint           | Primary key                       |
| user_id           | bigint           | Foreign key to users              |
| account_id        | bigint           | Foreign key to accounts           |
| category_id       | bigint           | Foreign key to categories         |
| type              | enum             | income, expense                   |
| date              | date             | Transaction date                  |
| amount            | decimal(15,2)    | Transaction amount                |
| note              | text nullable    | Optional description              |
| counterparty      | varchar nullable | Who/what the transaction was with |
| transfer_group_id | uuid nullable    | Links transfer pairs              |
| created_at        | timestamp        |                                   |
| updated_at        | timestamp        |                                   |

**Indexes:**

-   `user_id, date`
-   `account_id`
-   `category_id`
-   `type`
-   `transfer_group_id`

### 5. `attachments`

File attachments for transactions (receipts, invoices, etc.)

| Field          | Type      | Description                 |
| -------------- | --------- | --------------------------- |
| id             | bigint    | Primary key                 |
| transaction_id | bigint    | Foreign key to transactions |
| path           | varchar   | File storage path           |
| original_name  | varchar   | Original filename           |
| size           | integer   | File size in bytes          |
| created_at     | timestamp |                             |
| updated_at     | timestamp |                             |

**Indexes:**

-   `transaction_id`

### 6. `recurring_rules`

Rules for automatic recurring transactions

| Field         | Type          | Description               |
| ------------- | ------------- | ------------------------- |
| id            | bigint        | Primary key               |
| user_id       | bigint        | Foreign key to users      |
| type          | enum          | income, expense           |
| account_id    | bigint        | Foreign key to accounts   |
| category_id   | bigint        | Foreign key to categories |
| amount        | decimal(15,2) | Transaction amount        |
| frequency     | enum          | daily, weekly, monthly    |
| start_date    | date          | When to start             |
| end_date      | date nullable | When to stop (optional)   |
| next_run_date | date          | Next execution date       |
| note          | text nullable | Description               |
| is_active     | boolean       | Rule status               |
| created_at    | timestamp     |                           |
| updated_at    | timestamp     |                           |

**Indexes:**

-   `user_id, is_active`
-   `next_run_date`

## Key Features

### Transfer Handling

Transfers between accounts are implemented as two linked transactions:

1. An **expense** transaction from the source account
2. An **income** transaction to the destination account
3. Both share the same `transfer_group_id` (UUID)

This approach ensures:

-   Full transparency in reports
-   Easy tracking of money movement
-   Consistent balance calculations

### Nested Categories

Categories support parent-child relationships for hierarchical organization:

-   Food & Dining
    -   Restaurant
    -   Groceries
-   Transportation
    -   Public Transport
    -   Fuel

### Recurring Transactions

The `recurring_rules` table enables automatic transaction generation:

-   Daily, weekly, or monthly frequencies
-   Optional end dates
-   Automatic rule deactivation when end date is reached

## Model Relationships

### User Model

```php
public function accounts(): HasMany
public function categories(): HasMany
public function transactions(): HasMany
public function recurringRules(): HasMany
```

### Account Model

```php
public function user(): BelongsTo
public function transactions(): HasMany
public function recurringRules(): HasMany
public function getCurrentBalance(): float
```

### Category Model

```php
public function user(): BelongsTo
public function parent(): BelongsTo
public function children(): HasMany
public function transactions(): HasMany
public function recurringRules(): HasMany
```

### Transaction Model

```php
public function user(): BelongsTo
public function account(): BelongsTo
public function category(): BelongsTo
public function attachments(): HasMany
public function transferPartner(): ?Transaction
public static function createTransfer(): array
public function isTransfer(): bool
```

### Attachment Model

```php
public function transaction(): BelongsTo
public function getUrl(): string
public function getHumanReadableSize(): string
```

### RecurringRule Model

```php
public function user(): BelongsTo
public function account(): BelongsTo
public function category(): BelongsTo
public function createTransaction(): Transaction
public function updateNextRunDate(): void
public function shouldRun(): bool
```

## Sample Data Structure

The system includes seeders that create:

-   Multiple account types (cash, bank, e-wallet)
-   Hierarchical income/expense categories
-   Sample transactions with proper relationships
-   Transfer examples

## Testing

Run the included test command to verify the system:

```bash
php artisan bookkeeping:test
```

This command demonstrates:

-   Account balance calculations
-   Category hierarchies
-   Transaction listing with transfers
-   Live transfer creation and balance updates

## Usage Examples

### Creating a Simple Transaction

```php
Transaction::create([
    'user_id' => $user->id,
    'account_id' => $account->id,
    'category_id' => $category->id,
    'type' => 'expense',
    'date' => now(),
    'amount' => 50000,
    'note' => 'Lunch at restaurant',
]);
```

### Creating a Transfer

```php
[$expense, $income] = Transaction::createTransfer(
    $user,
    $fromAccount,
    $toAccount,
    100000,
    'Monthly savings transfer'
);
```

### Getting Account Balance

```php
$balance = $account->getCurrentBalance();
```

### Finding Transfer Partner

```php
$transfer = $transaction->transferPartner();
```

This design provides a solid foundation for a bookkeeping system that can scale from simple personal finance to more complex business accounting needs.
