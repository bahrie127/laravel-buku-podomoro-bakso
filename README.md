# Buku Finansial Podomoro

A modern, multi-user bookkeeping and financial management system built with Laravel 12 and Filament v4. **Buku Finansial Podomoro** provides a comprehensive solution for managing bakso business finances with an intuitive admin interface designed specifically for Indonesian users.

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-v4-orange?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.3+-blue?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## ✨ Features

### 📊 Dashboard & Analytics

-   **Financial Overview**: Income, expenses, net profit, and total balance cards with clickable navigation
-   **Account Balance Widget**: Real-time overview of all account balances
-   **Interactive Charts**: Visual representation of financial data

### 💰 Account Management

-   Create and manage multiple accounts (Cash, Bank, E-Wallet, etc.)
-   Track account balances with automatic calculations
-   Multi-user support with data isolation
-   Live auto-formatting for monetary values

### 🏷️ Category Management

-   Hierarchical category system (parent/child categories)
-   Separate categories for income and expenses
-   User-specific categories for data privacy

### 📝 Transaction Management

-   Create income and expense transactions
-   Auto-formatting for amounts (Indonesian Rupiah format)
-   File attachments for receipts and documents
-   Notes and descriptions for detailed tracking
-   Real-time balance calculations

### 🔐 Multi-User System

-   User registration and authentication
-   Data isolation between users
-   Secure access control for all resources

### 🎨 Modern UI/UX

-   Built with Filament v4 for modern admin interface
-   Responsive design for mobile and desktop
-   Intuitive navigation with grouped menu items
-   Live form validation and formatting

## 🚀 Installation

### Prerequisites

-   PHP 8.3 or higher
-   Composer
-   Node.js & NPM
-   MySQL/PostgreSQL/SQLite

### Setup Instructions

1. **Clone the repository**

    ```bash
    git clone https://github.com/bahrie127/laravel-bookkeeping-system.git
    cd laravel-bookkeeping-system
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install JavaScript dependencies**

    ```bash
    npm install
    ```

4. **Environment configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Database setup**

    ```bash
    # Configure your database in .env file
    php artisan migrate
    php artisan db:seed
    ```

6. **Build assets**

    ```bash
    npm run build
    # or for development
    npm run dev
    ```

7. **Start the application**

    ```bash
    php artisan serve
    ```

8. **Access the application**
    - Visit: `http://localhost:8000/admin`
    - Register a new account or use seeded data

## 🛠️ Tech Stack

-   **Backend**: Laravel 12, PHP 8.3
-   **Frontend**: Filament v4, Alpine.js, Tailwind CSS
-   **Database**: MySQL/PostgreSQL/SQLite
-   **Authentication**: Laravel Sanctum
-   **File Storage**: Laravel Storage
-   **Build Tool**: Vite

## 📁 Project Structure

```
app/
├── Filament/Admin/
│   ├── Resources/          # Filament resources
│   │   ├── Accounts/
│   │   ├── Categories/
│   │   └── Transactions/
│   └── Widgets/           # Dashboard widgets
├── Models/                # Eloquent models
└── Providers/            # Service providers

database/
├── migrations/           # Database migrations
├── seeders/             # Database seeders
└── factories/           # Model factories
```

## 🔧 Configuration

### Timezone Configuration

The application is configured for Indonesian timezone (WIB/Asia/Jakarta). You can modify this in `config/app.php`:

```php
'timezone' => 'Asia/Jakarta',
```

### Currency Settings

Currently configured for Indonesian Rupiah (IDR). Modify currency settings in the table configurations as needed.

## 🚀 Usage

### Getting Started

1. **Register**: Create a new user account
2. **Setup Accounts**: Add your bank accounts, cash, e-wallets
3. **Create Categories**: Set up income and expense categories
4. **Record Transactions**: Start logging your financial activities
5. **Monitor Dashboard**: Track your financial health

### Key Features Guide

-   **Auto-formatting**: Amount fields automatically format with thousand separators
-   **File Attachments**: Upload receipts and documents to transactions
-   **Search & Filter**: Find transactions quickly with powerful search
-   **Data Export**: Export transaction data for external analysis

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

### Development Setup

```bash
# Run in development mode
npm run dev
php artisan serve

# Run tests
php artisan test

# Code formatting
vendor/bin/pint
```

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 About the Developer

**Bahri** - Full Stack Developer & Programming Instructor

I'm passionate about creating useful applications and sharing knowledge with the developer community. This bookkeeping system was built to demonstrate modern Laravel and Filament capabilities while solving real-world financial management needs.

### 🌐 Connect with Me

-   **LinkedIn**: [linkedin.com/in/bahrie](https://linkedin.com/in/bahrie)
-   **Instagram**: [@codewithbahri](https://instagram.com/codewithbahri)
-   **GitHub**: [github.com/bahrie127](https://github.com/bahrie127)
-   **YouTube**: [@codewithbahri](https://youtube.com/@codewithbahri)
-   **WhatsApp Channel**: [Programming Tips & Tutorials](https://whatsapp.com/channel/0029Vb0ucRx7oQhVmCVypC1Y)
-   **WhatsApp**: [+62 856-4089-9224](https://wa.me/6285640899224)

### 💝 Support the Project

If this project helps you, please consider:

-   ⭐ Starring the repository
-   🐛 Reporting bugs and issues
-   💡 Suggesting new features
-   📖 Improving documentation
-   🔗 Sharing with others

### 📚 Learning Resources

Check out my YouTube channel [@codewithbahri](https://youtube.com/@codewithbahri) for Laravel tutorials, tips, and programming content in Indonesian.

---

**Made with ❤️ by [Bahri](https://github.com/bahrie127)**

_Happy Coding! 🚀_
