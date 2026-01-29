<?php
/**
 * EXAMPLE CONSTANTS CONFIGURATION
 * Copy this file to constants.php and update with your values
 */

// Website Settings
define('SITE_NAME', 'ShopHub');
define('SITE_URL', 'https://yourdomain.com'); // Change to your actual domain
define('SITE_TITLE', 'ShopHub - Modern E-commerce Platform');
define('SITE_DESCRIPTION', 'Buy products online with best prices and discounts');

// Theme Colors (Customize these)
define('PRIMARY_COLOR', '#ff7a00');        // Orange
define('SECONDARY_COLOR', '#ff8c1a');      // Light Orange
define('LIGHT_BG', '#ffffff');             // White
define('DARK_BG', '#000000');              // Black

// Security Settings
define('SESSION_TIMEOUT', 3600);           // 1 hour in seconds
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('CSRF_TOKEN_LENGTH', 32);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5242880);        // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// ============================================
// PAYMENT GATEWAY CONFIGURATION (Cashfree)
// ============================================
define('CASHFREE_MODE', 'SANDBOX');        // Use 'SANDBOX' for testing, 'PROD' for live
define('CASHFREE_APP_ID', 'your_app_id_here');
define('CASHFREE_SECRET_KEY', 'your_secret_key_here');

// To get Cashfree credentials:
// 1. Visit https://dashboard.cashfree.com/
// 2. Sign up or log in
// 3. Go to Settings > API Keys
// 4. Copy your APP ID and SECRET KEY
// 5. Use SANDBOX mode for testing
// 6. Switch to PROD mode when going live

// ============================================
// EMAIL CONFIGURATION (Zoho SMTP)
// ============================================
define('EMAIL_HOST', 'smtp.zoho.com');
define('EMAIL_PORT', 465);                 // Use 465 for SSL, 587 for TLS
define('EMAIL_USER', 'your_zoho_email@zoho.com');  // Your Zoho email
define('EMAIL_PASS', 'your_zoho_app_password');    // Your Zoho app password
define('EMAIL_FROM_NAME', SITE_NAME);

// To set up Zoho SMTP:
// 1. Visit https://www.zoho.com/mail/
// 2. Create a free account
// 3. Go to Settings > Apps > Connected Apps
// 4. Generate app-specific password
// 5. Use that password above
// 6. Update email address and password

// ============================================
// ADMIN & SUPPORT SETTINGS
// ============================================
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('SUPPORT_EMAIL', 'support@yourdomain.com');

// ============================================
// ORDER STATUS CONSTANTS
// ============================================
define('ORDER_STATUS_PLACED', 'placed');
define('ORDER_STATUS_CONFIRMED', 'confirmed');
define('ORDER_STATUS_PACKED', 'packed');
define('ORDER_STATUS_SHIPPED', 'shipped');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');

// ============================================
// PAYMENT STATUS CONSTANTS
// ============================================
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_COMPLETED', 'completed');
define('PAYMENT_STATUS_FAILED', 'failed');

// ============================================
// USER ROLES
// ============================================
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// ============================================
// SHIPPING SETTINGS
// ============================================
define('DEFAULT_SHIPPING_COST', 50);       // ₹50
define('FREE_SHIPPING_ABOVE', 500);        // ₹500

// ============================================
// NOTES FOR CONFIGURATION
// ============================================

/*
 * 1. SITE_URL:
 *    - For local testing: http://localhost:8000
 *    - For production: https://yourdomain.com (use HTTPS)
 *    - Must NOT have trailing slash
 *
 * 2. EMAIL CONFIGURATION:
 *    - Zoho is recommended for reliability
 *    - Gmail also works (less secure, needs app password)
 *    - Never use plain text passwords in code
 *
 * 3. CASHFREE PAYMENT:
 *    - SANDBOX: Test without real money
 *    - PROD: Live transactions (money will be charged)
 *    - Always test in SANDBOX first
 *    - Webhook URL: https://yourdomain.com/api/webhook/cashfree.php
 *
 * 4. SECURITY:
 *    - Change SESSION_TIMEOUT for more/less security
 *    - Keep password hash algorithm as PASSWORD_BCRYPT
 *    - Never commit this file to public repository
 *
 * 5. FILE UPLOADS:
 *    - MAX_UPLOAD_SIZE: Adjust based on your hosting limits
 *    - ALLOWED_IMAGE_TYPES: Add/remove file types as needed
 *    - Ensure /uploads directory is writable
 *
 * 6. DATABASE:
 *    - Always use HTTPS in production
 *    - Keep DB credentials private
 *    - Regular backups recommended
 */

?>
