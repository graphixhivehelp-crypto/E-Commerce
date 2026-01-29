<?php
/**
 * Core Helper Functions
 */

// Require config files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Indian format)
 */
function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone));
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 11]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $user;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    if (isLoggedIn()) {
        $user = getCurrentUser();
        return $user && $user['role'] === 'admin';
    }
    return false;
}

/**
 * Redirect to login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . SITE_URL . "/pages/login.php");
        exit();
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . SITE_URL . "/pages/404.php");
        exit();
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate OTP (6 digits)
 */
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Format currency in Indian Rupees
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2, '.', ',');
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Get discount percentage
 */
function getDiscountedPrice($originalPrice, $discountPercentage) {
    return $originalPrice - ($originalPrice * $discountPercentage / 100);
}

/**
 * Calculate discount amount
 */
function getDiscountAmount($originalPrice, $discountPercentage) {
    return $originalPrice * $discountPercentage / 100;
}

/**
 * Get product price
 */
function getProductPrice($product) {
    if ($product['discount_percentage'] > 0) {
        return getDiscountedPrice($product['price'], $product['discount_percentage']);
    }
    return $product['price'];
}

/**
 * Create slug from string
 */
function createSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Upload file
 */
function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'webp']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds limit'];
    }

    $ext = getFileExtension($file['name']);
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('img_', true) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'error' => 'Failed to save file'];
}

/**
 * Get product image
 */
function getProductImage($productId, $conn) {
    $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();
    return $image ? $image['image_url'] : '/assets/images/placeholder.png';
}

/**
 * Get cart count
 */
function getCartCount() {
    $count = 0;
    if (isLoggedIn()) {
        $conn = getConnection();
        $userId = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];
        $stmt->close();
        $conn->close();
    } elseif (isset($_SESSION['guest_cart'])) {
        $count = count($_SESSION['guest_cart']);
    }
    return $count;
}

/**
 * Generate order number
 */
function generateOrderNumber() {
    return 'ORD-' . strtoupper(date('YmdHis')) . '-' . random_int(1000, 9999);
}

/**
 * Send email via Zoho using OAuth2
 */
function sendEmailViaZoho($to, $subject, $message, $isHTML = true) {
    try {
        // Get Zoho access token
        $accessToken = getZohoAccessToken();
        if (!$accessToken) {
            // Fallback to SMTP if OAuth fails
            return sendEmailViaSMTP($to, $subject, $message, $isHTML);
        }

        // Prepare email content
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $emailData = [
            'fromAddress' => ZOHO_EMAIL,
            'toAddress' => $to,
            'subject' => $subject,
            'content' => $message,
            'htmlContent' => $isHTML ? $message : null
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ZOHO_MAIL_API . ZOHO_USER_ID . '/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    } catch (Exception $e) {
        // Fallback to SMTP
        return sendEmailViaSMTP($to, $subject, $message, $isHTML);
    }
}

/**
 * Get Zoho OAuth2 Access Token
 */
function getZohoAccessToken() {
    // Check if token is cached and still valid
    $cacheFile = sys_get_temp_dir() . '/zoho_token_' . md5(ZOHO_CLIENT_ID);
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (isset($cached['expires_at']) && time() < $cached['expires_at']) {
            return $cached['access_token'];
        }
    }

    // Request new token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ZOHO_TOKEN_ENDPOINT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'client_credentials',
        'client_id' => ZOHO_CLIENT_ID,
        'client_secret' => ZOHO_CLIENT_SECRET,
        'scope' => 'ZohoMail.accounts.READ ZohoMail.messages.CREATE'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            // Cache the token
            $tokenData = [
                'access_token' => $data['access_token'],
                'expires_at' => time() + ($data['expires_in'] ?? 3600)
            ];
            file_put_contents($cacheFile, json_encode($tokenData));
            return $data['access_token'];
        }
    }

    return false;
}

/**
 * Send email via SMTP (Fallback)
 */
function sendEmailViaSMTP($to, $subject, $message, $isHTML = true) {
    try {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: " . ($isHTML ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
        $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_USER . ">\r\n";
        $headers .= "Reply-To: " . EMAIL_USER . "\r\n";

        return mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Send email (Main function - uses Zoho with SMTP fallback)
 */
function sendEmail($to, $subject, $message, $isHTML = true) {
    // Try Zoho OAuth2 first
    if (sendEmailViaZoho($to, $subject, $message, $isHTML)) {
        return true;
    }
    
    // Fallback to SMTP
    return sendEmailViaSMTP($to, $subject, $message, $isHTML);
}

/**
 * Log activity
 */
function logActivity($action, $details = '') {
    // Can be extended to log to database or file
    // For now, just a placeholder
}

/**
 * Get settings
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $stmt->close();
        $conn->close();
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * JSON response
 */
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

?>
