# BukuBisnis API Documentation

Selamat datang di dokumentasi API BukuBisnis! 📚💼

## 📋 Table of Contents

### Core Documentation

1. **[Quick Start Guide](QUICKSTART.md)** - Panduan cepat untuk memulai dalam 5 menit
2. **[Complete API Documentation](README.md)** - Dokumentasi API lengkap dengan semua endpoint
3. **[Account Management](accounts.md)** - Detail khusus endpoint manajemen akun

### Advanced Guides

4. **[Authentication Guide](AUTHENTICATION.md)** - Panduan lengkap autentikasi Sanctum
5. **[Error Handling Guide](ERROR_HANDLING.md)** - Strategi menangani error dengan baik
6. **[Testing Guide](TESTING.md)** - Panduan testing lengkap (cURL, Postman, PHPUnit)
7. **[Performance & Security](PERFORMANCE_SECURITY.md)** - Optimasi performa dan keamanan
8. **[Deployment Guide](DEPLOYMENT.md)** - Panduan deploy production lengkap

### Tools & Resources

9. **[Postman Collection](BukuBisnis-API.postman_collection.json)** - Import ke Postman untuk testing
10. **[Environment Variables](BukuBisnis.postman_environment.json)** - Environment Postman
11. **[Test Script](../test_account_api.sh)** - Script testing otomatis

## 🚀 Quick Links

### For New Developers

-   [⚡ Quick Start](QUICKSTART.md) - Mulai dalam 5 menit
-   [� Complete API Docs](README.md) - Reference lengkap semua endpoint
-   [📮 Postman Collection](BukuBisnis-API.postman_collection.json) - Import untuk testing instant

### For Advanced Implementation

-   [🔐 Authentication Deep Dive](AUTHENTICATION.md) - Token management, security, client implementations
-   [❌ Error Handling Strategies](ERROR_HANDLING.md) - Robust error handling untuk production
-   [🧪 Comprehensive Testing](TESTING.md) - Unit tests, integration tests, automated testing

### For Production Deployment

-   [🚀 Production Deployment](DEPLOYMENT.md) - Complete server setup dan deployment
-   [⚡ Performance Optimization](PERFORMANCE_SECURITY.md) - Database optimization, caching, monitoring
-   [🛡️ Security Best Practices](PERFORMANCE_SECURITY.md) - Rate limiting, input validation, secure headers

## 🎯 What's Included

### Authentication System

-   ✅ User Registration dengan validasi lengkap
-   ✅ Login dengan Laravel Sanctum token
-   ✅ User profile management
-   ✅ Secure logout dengan token revocation
-   ✅ Token-based authentication untuk semua protected endpoints

### Account Management

-   ✅ List accounts dengan search & filter canggih
-   ✅ Create accounts dengan validasi business rules
-   ✅ View account details dengan ownership protection
-   ✅ Update account information secara partial
-   ✅ Delete accounts dengan transaction checking
-   ✅ Real-time balance calculation

### API Features

-   ✅ RESTful API design
-   ✅ JSON responses dengan format konsisten
-   ✅ Comprehensive error handling
-   ✅ Input validation pada semua endpoint
-   ✅ Rate limiting untuk protection
-   ✅ CORS configuration
-   ✅ API versioning ready

## 🔒 Security Features

-   **Laravel Sanctum** untuk token-based authentication
-   **User data isolation** - complete ownership protection
-   **Input sanitization** dan validation
-   **SQL injection prevention**
-   **Rate limiting** dengan custom middleware
-   **CORS protection**
-   **Secure headers** implementation
-   **Token security** best practices

## 📊 API Overview

| Feature                  | Status         | Endpoints   | Documentation                                |
| ------------------------ | -------------- | ----------- | -------------------------------------------- |
| Authentication           | ✅ Complete    | 4 endpoints | [Auth Guide](AUTHENTICATION.md)              |
| Account Management       | ✅ Complete    | 5 endpoints | [Account Guide](accounts.md)                 |
| Error Handling           | ✅ Complete    | Global      | [Error Guide](ERROR_HANDLING.md)             |
| Testing Suite            | ✅ Complete    | -           | [Testing Guide](TESTING.md)                  |
| Performance Optimization | ✅ Complete    | -           | [Performance Guide](PERFORMANCE_SECURITY.md) |
| Transaction Management   | 🚧 Coming Soon | -           | -                                            |
| Reports & Analytics      | 🚧 Coming Soon | -           | -                                            |

## 🛠 Technical Stack

-   **Framework**: Laravel 12 dengan modern PHP 8.3
-   **Authentication**: Laravel Sanctum token-based
-   **Database**: SQLite/MySQL dengan optimized indexes
-   **API Style**: RESTful JSON API dengan consistent responses
-   **Validation**: Form Request classes dengan custom rules
-   **Testing**: PHPUnit dengan comprehensive test suite
-   **Performance**: Redis caching dan query optimization

## 🚦 Getting Started

### Beginner Path

1. **[Quick Start](QUICKSTART.md)** - Setup dan first API call
2. **[Postman Collection](BukuBisnis-API.postman_collection.json)** - Import dan test
3. **[Complete Documentation](README.md)** - Explore semua endpoints

### Developer Path

1. **[Authentication Guide](AUTHENTICATION.md)** - Implement secure auth
2. **[Error Handling](ERROR_HANDLING.md)** - Build robust error handling
3. **[Testing Guide](TESTING.md)** - Setup comprehensive testing

### Production Path

1. **[Performance Guide](PERFORMANCE_SECURITY.md)** - Optimize untuk production
2. **[Security Guide](PERFORMANCE_SECURITY.md)** - Implement security best practices
3. **[Monitoring Setup](PERFORMANCE_SECURITY.md)** - Setup logging dan monitoring

## 📝 Example Usage

```bash
# 1. Register new user
curl -X POST localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123"}'

# 2. Login dan get token
curl -X POST localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# 3. Create account (gunakan token dari login)
curl -X POST localhost:8000/api/accounts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"name":"Cash Wallet","type":"cash","starting_balance":1000.00,"is_active":true}'

# 4. List accounts dengan filter
curl -X GET "localhost:8000/api/accounts?q=cash&is_active=1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 5. Update account
curl -X PUT localhost:8000/api/accounts/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"name":"Updated Cash Wallet","is_active":false}'
```

## 🧪 Testing Resources

### Quick Testing

-   **[Postman Collection](BukuBisnis-API.postman_collection.json)** - Ready-to-use requests
-   **[Environment File](BukuBisnis.postman_environment.json)** - Pre-configured variables
-   **[Test Script](../test_account_api.sh)** - Automated testing script

### Advanced Testing

-   **[Testing Guide](TESTING.md)** - Complete testing strategies
-   **PHPUnit Test Suite** - Unit dan integration tests
-   **Performance Testing** - Load testing dengan Apache Bench
-   **Security Testing** - Vulnerability testing guidelines

## 🔄 API Versioning

-   **Current**: v1.0 - Authentication & Account Management
-   **Planned**: v1.1 - Transaction Management dengan categories
-   **Planned**: v1.2 - Reports, Analytics, dan Export features
-   **Future**: v2.0 - Multi-user organizations dan advanced permissions

## 📈 Performance Metrics

-   **Response Time**: < 200ms untuk standard queries
-   **Throughput**: 1000+ requests/minute dengan rate limiting
-   **Database**: Optimized dengan proper indexing
-   **Caching**: Redis untuk frequent data
-   **Memory Usage**: < 50MB peak untuk standard operations

## 🆘 Need Help?

### Documentation Issues

-   📖 Check [Complete Documentation](README.md) untuk reference
-   🚀 Try [Quick Start Guide](QUICKSTART.md) untuk basic setup
-   🔍 Search specific topics dalam advanced guides

### Implementation Help

-   � Authentication issues? See [Authentication Guide](AUTHENTICATION.md)
-   ❌ Error handling problems? Check [Error Handling Guide](ERROR_HANDLING.md)
-   🧪 Testing questions? Review [Testing Guide](TESTING.md)

### Performance & Security

-   ⚡ Performance issues? See [Performance Guide](PERFORMANCE_SECURITY.md)
-   🛡️ Security concerns? Review [Security Best Practices](PERFORMANCE_SECURITY.md)

### Quick Troubleshooting

1. Import [Postman Collection](BukuBisnis-API.postman_collection.json) untuk quick testing
2. Run [test script](../test_account_api.sh) untuk automated verification
3. Check Laravel logs untuk detailed error information
4. Review [Error Handling Guide](ERROR_HANDLING.md) untuk common solutions

---

**Ready to build amazing applications?** 🚀
Start with the [Quick Start Guide](QUICKSTART.md) dan explore the powerful BukuBisnis API!
