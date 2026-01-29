# ShopHub - Modern E-Commerce Platform

A professional, feature-rich Flipkart-style e-commerce platform built with PHP and MySQL, optimized for InfinityFree shared hosting.

## Features

### User Experience
- Product browsing with advanced filters
- Live product search with autocomplete
- Wishlist system for saving products
- Shopping cart with quantity management
- Order tracking and history
- Product reviews and ratings
- User accounts and profiles

### Shopping & Payments
- Multiple payment options (COD, Cashfree)
- Secure checkout process
- Address management
- Coupon/discount system
- Order notifications via email
- Free shipping on orders above ₹500

### Admin Panel
- Dashboard with sales analytics
- Product management
- User management
- Order management
- Category management
- Coupon management
- Website settings

### Security Features
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- CSRF token validation
- Password hashing with bcrypt
- Secure session management
- File upload validation

## Requirements

### Minimum
- PHP 7.4+
- MySQL 5.7+
- 500MB disk space
- Apache with mod_rewrite

### Recommended
- PHP 8.0+
- MySQL 8.0+
- 1GB disk space
- HTTPS support

## Installation

### 1. Database Setup

1. Log in to your hosting control panel
2. Create a new MySQL database
3. Import `/config/database.sql`:
   - Using phpMyAdmin: Select database → Import tab → Choose file
   - Using CLI: `mysql -u user -p database < config/database.sql`

### 2. Configuration

1. Update `/config/database.php`:
   ```php
   define('DB_HOST', 'your_host');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'your_database');
   ```

2. Update `/config/constants.php`:
   ```php
   define('SITE_URL', 'https://yourdomain.com');
   define('SITE_NAME', 'Your Store Name');
   
   // Cashfree Payment Gateway
   define('CASHFREE_APP_ID', 'your_app_id');
   define('CASHFREE_SECRET_KEY', 'your_secret_key');
   
   // Zoho Email
   define('ZOHO_EMAIL', 'your_email@domain.com');
   ```

### 3. Upload Files

1. Upload all files to your hosting (via FTP/SFTP)
2. Set permissions:
   - Directories: `755`
   - Files: `644`
   - `/uploads/`: `755`

### 4. Create Admin Account

1. Open phpMyAdmin
2. Insert into `users` table:
   ```sql
   INSERT INTO users (name, email, phone, password, role, status)
   VALUES ('Admin', 'admin@example.com', '9876543210', 
           '$2y$11$...bcrypt_hash...', 'admin', 'active');
   ```

### 5. Access Your Store

- Frontend: `https://yourdomain.com/pages/index.php`
- Admin: `https://yourdomain.com/admin/dashboard.php`

## Project Structure

```
├── config/                 # Configuration files
│   ├── database.php       # Database connection
│   ├── constants.php      # Global settings
│   └── database.sql       # Database schema
├── includes/             # Core functionality
│   ├── header.php        # Navigation
│   ├── footer.php        # Footer
│   ├── functions.php     # Utility functions
│   └── classes.php       # Classes (Auth, Product, Payment)
├── pages/                # Frontend pages
│   ├── index.php         # Homepage
│   ├── products.php      # Product listing
│   ├── login.php         # User login
│   ├── register.php      # User registration
│   ├── cart.php          # Shopping cart
│   └── checkout.php      # Checkout process
├── api/                  # API endpoints
│   ├── payment.php       # Payment processing
│   ├── cart.php          # Cart operations
│   ├── wishlist.php      # Wishlist operations
│   ├── search.php        # Product search
│   └── get-cart-count.php # Cart count
├── admin/                # Admin panel
│   └── dashboard.php     # Admin dashboard
├── assets/               # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── images/           # Images
├── uploads/              # User uploads
└── .htaccess            # Apache configuration
```

## Core Functions

### Authentication
```php
hashPassword($password)           // Hash password
verifyPassword($password, $hash)   // Verify password
generateCSRFToken()               // Generate CSRF token
getCurrentUser()                  // Get logged-in user
isLoggedIn()                      // Check if user logged in
isAdmin()                         // Check if user is admin
```

### Email
```php
sendEmail($to, $subject, $message, $isHTML)     // Send email (Zoho OAuth2 + SMTP fallback)
sendEmailViaZoho($to, $subject, $message)       // Direct Zoho API
sendEmailViaSMTP($to, $subject, $message)       // Direct SMTP
getZohoAccessToken()                            // Get OAuth token
```

### Products
```php
getProductPrice($product)         // Get final price with discount
getDiscountedPrice($original, $percentage)  // Calculate discount
formatCurrency($amount)           // Format as currency (₹)
```

### Payments
```php
$cashfree = new CashfreePayment();
$cashfree->createPaymentSession($orderId, $amount, $email, $phone);
$cashfree->verifyPayment($orderId);
$cashfree->refundPayment($orderId, $amount);
```

## Payment Integration

### Cashfree Payment Gateway

**Sandbox Testing:**
- Card: `4111 1111 1111 1111`
- Expiry: `12/25`
- CVV: `123`
- OTP: `123456`

**Production Setup:**
1. Get production credentials from Cashfree dashboard
2. Update in `/config/constants.php`:
   ```php
   define('CASHFREE_MODE', 'PROD');
   define('CASHFREE_APP_ID', 'production_app_id');
   define('CASHFREE_SECRET_KEY', 'production_secret_key');
   ```
3. Configure webhook URL: `https://yourdomain.com/api/payment.php?action=webhook`

## Email Integration

### Zoho Mail

**Setup:**
1. Email service: `noreply@yourdomain.com`
2. OAuth2 credentials configured in `/config/constants.php`
3. SMTP fallback for reliability

**Sending Emails:**
```php
sendEmail('user@example.com', 'Welcome!', '<h1>Welcome to ShopHub</h1>', true);
```

## Database Schema

### Main Tables
- **users** - User accounts and authentication
- **products** - Product catalog
- **categories** - Product categories
- **cart** - Shopping cart items
- **orders** - Customer orders
- **order_items** - Items in each order
- **wishlist** - Saved products
- **reviews** - Product reviews
- **coupons** - Discount codes
- **shipments** - Order shipment tracking
- **settings** - Website configuration

## Security Checklist

- [x] SQL injection prevention (prepared statements)
- [x] XSS protection (sanitization & escaping)
- [x] CSRF token validation
- [x] Password hashing (bcrypt)
- [x] Session security (httponly, samesite)
- [x] Input validation
- [x] File upload validation
- [x] Apache security headers (.htaccess)

## Performance Optimizations

- Database indexing on frequent queries
- Lazy loading for product images
- Browser caching configured
- Gzip compression enabled
- Optimized queries with pagination
- Session token caching

## Responsive Design

- Mobile-first approach
- 6 responsive breakpoints:
  - Extra Small (< 576px)
  - Small (≥ 576px)
  - Medium (≥ 768px)
  - Large (≥ 992px)
  - Extra Large (≥ 1200px)
  - Ultra Large (≥ 1400px)
- Dark mode support
- Accessibility features

## API Endpoints

### Cart Operations
```
POST /api/cart.php
- action: add, update, remove
- product_id, quantity, size, color
```

### Wishlist Operations
```
POST /api/wishlist.php
- action: add, remove
- product_id
```

### Payment Processing
```
POST /api/payment.php?action=create_session
POST /api/payment.php?action=verify
POST /api/payment.php?action=webhook
```

### Search
```
GET /api/search.php?q=search_term
```

## Customization

### Change Theme Colors
Edit `/config/constants.php`:
```php
define('PRIMARY_COLOR', '#ff7a00');      // Orange
define('SECONDARY_COLOR', '#ff8c1a');    // Light Orange
```

### Update Site Information
Edit `/config/constants.php`:
```php
define('SITE_NAME', 'Your Store');
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
```

### Add New Pages
1. Create file in `/pages/your-page.php`
2. Include header and footer:
   ```php
   <?php require_once dirname(__DIR__) . '/includes/header.php'; ?>
   <!-- Your content -->
   <?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
   ```

## Troubleshooting

### Blank Page
- Check error logs in hosting control panel
- Ensure database connection is correct
- Verify file permissions

### Emails Not Sending
- Check internet connection
- Verify Zoho credentials in constants.php
- Check email logs in Zoho dashboard
- Verify SMTP settings

### Payment Not Working
- Use correct test credentials
- Check Cashfree dashboard for order status
- Verify webhook URL is configured
- Check server logs for errors

### Database Connection Failed
- Verify database credentials
- Ensure database exists
- Check MySQL is running
- Verify user permissions

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

Available for educational and commercial use.

## Support

For issues or questions:
1. Check error logs in your hosting panel
2. Review code comments in files
3. Check Zoho/Cashfree documentation
4. Contact your hosting provider

---

**Version:** 1.0  
**Last Updated:** January 29, 2026  
**Status:** Production Ready