<?php
/**
 * Session Manager Class
 */

class SessionManager {
    
    /**
     * Start session with security settings
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_TIMEOUT,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }
    
    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Delete session key
     */
    public static function delete($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
    
    /**
     * Regenerate session ID (security)
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }
}

/**
 * User Authentication Class
 */
class Auth {
    
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Register new user
     */
    public function register($name, $email, $phone, $password) {
        // Validate inputs
        if (!validateEmail($email)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }
        
        if (!validatePhone($phone)) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters'];
        }
        
        // Check if user exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'error' => 'User already exists'];
        }
        $stmt->close();
        
        // Hash password and create user
        $hashedPassword = hashPassword($password);
        $otp = generateOTP();
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, phone, password, otp, otp_expiry) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $phone, $hashedPassword, $otp, $otpExpiry);
        
        if ($stmt->execute()) {
            $stmt->close();
            // Send OTP email
            $this->sendOTPEmail($email, $otp);
            return ['success' => true, 'message' => 'User registered. OTP sent to email'];
        }
        
        $stmt->close();
        return ['success' => false, 'error' => 'Registration failed'];
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'error' => 'User not found'];
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user['status'] === 'blocked') {
            return ['success' => false, 'error' => 'User account is blocked'];
        }
        
        if (!verifyPassword($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid password'];
        }
        
        // Update last login
        $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();
        
        // Set session
        SessionManager::regenerate();
        SessionManager::set('user_id', $user['id']);
        SessionManager::set('user_role', $user['role']);
        SessionManager::set('user_email', $user['email']);
        
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($email, $otp) {
        $stmt = $this->conn->prepare("SELECT id, otp, otp_expiry FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'error' => 'User not found'];
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user['otp'] !== $otp) {
            return ['success' => false, 'error' => 'Invalid OTP'];
        }
        
        if (strtotime($user['otp_expiry']) < time()) {
            return ['success' => false, 'error' => 'OTP expired'];
        }
        
        // Mark email as verified
        $stmt = $this->conn->prepare("UPDATE users SET email_verified = 1, otp = NULL, otp_expiry = NULL WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();
        
        return ['success' => true, 'message' => 'Email verified successfully'];
    }
    
    /**
     * Send OTP email
     */
    private function sendOTPEmail($email, $otp) {
        $subject = "Email Verification - " . SITE_NAME;
        $message = "<h2>Welcome to " . SITE_NAME . "</h2>";
        $message .= "<p>Your OTP for email verification is: <strong>" . $otp . "</strong></p>";
        $message .= "<p>This OTP will expire in 10 minutes.</p>";
        
        sendEmail($email, $subject, $message);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        SessionManager::destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }
}

/**
 * Product Class
 */
class Product {
    
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get all products with pagination
     */
    public function getProducts($page = 1, $limit = PRODUCTS_PER_PAGE, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM products WHERE status = 'active'";
        
        if (!empty($filters['category_id'])) {
            $query .= " AND category_id = " . intval($filters['category_id']);
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $this->conn->real_escape_string($filters['search']) . '%';
            $query .= " AND (name LIKE '$search' OR description LIKE '$search')";
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND price >= " . floatval($filters['min_price']);
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND price <= " . floatval($filters['max_price']);
        }
        
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $query .= " ORDER BY price ASC";
                    break;
                case 'price_desc':
                    $query .= " ORDER BY price DESC";
                    break;
                case 'newest':
                    $query .= " ORDER BY created_at DESC";
                    break;
                case 'rating':
                    $query .= " ORDER BY rating DESC";
                    break;
                default:
                    $query .= " ORDER BY created_at DESC";
            }
        } else {
            $query .= " ORDER BY created_at DESC";
        }
        
        $query .= " LIMIT $offset, $limit";
        
        $result = $this->conn->query($query);
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    /**
     * Get product by ID
     */
    public function getProductById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            // Get images
            $stmt = $this->conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $product['images'] = $images;
        }
        
        return $product;
    }
    
    /**
     * Get total product count
     */
    public function getTotalCount($filters = []) {
        $query = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
        
        if (!empty($filters['category_id'])) {
            $query .= " AND category_id = " . intval($filters['category_id']);
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . $this->conn->real_escape_string($filters['search']) . '%';
            $query .= " AND (name LIKE '$search' OR description LIKE '$search')";
        }
        
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
}

/**
 * Cashfree Payment Gateway Class
 */
class CashfreePayment {
    
    private $appId;
    private $secretKey;
    private $apiMode;
    private $baseUrl;
    
    public function __construct() {
        $this->appId = CASHFREE_APP_ID;
        $this->secretKey = CASHFREE_SECRET_KEY;
        $this->apiMode = CASHFREE_MODE;
        
        if ($this->apiMode === 'PROD') {
            $this->baseUrl = 'https://api.cashfree.com/pg';
        } else {
            $this->baseUrl = 'https://sandbox.cashfree.com/pg';
        }
    }
    
    /**
     * Create payment session
     */
    public function createPaymentSession($orderId, $amount, $customerEmail, $customerPhone, $returnUrl = '') {
        try {
            $payload = [
                'order_id' => $orderId,
                'order_amount' => $amount,
                'order_currency' => 'INR',
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'order_note' => 'Payment for Order ' . $orderId,
                'return_url' => $returnUrl ?: SITE_URL . '/pages/checkout.php?status=success'
            ];
            
            $headers = [
                'X-Client-Id: ' . $this->appId,
                'X-Client-Secret: ' . $this->secretKey,
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if ($httpCode === 200 && isset($data['cf_order_id'])) {
                return [
                    'success' => true,
                    'order_id' => $data['cf_order_id'],
                    'payment_link' => $data['payment_link'] ?? '',
                    'session_id' => $data['order_id'] ?? ''
                ];
            }
            
            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to create payment session'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify payment
     */
    public function verifyPayment($orderId) {
        try {
            $headers = [
                'X-Client-Id: ' . $this->appId,
                'X-Client-Secret: ' . $this->secretKey,
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/orders/' . $orderId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'order_id' => $data['order_id'] ?? '',
                    'order_amount' => $data['order_amount'] ?? 0,
                    'payment_status' => $data['order_status'] ?? '',
                    'cf_order_id' => $data['cf_order_id'] ?? ''
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to verify payment'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Refund payment
     */
    public function refundPayment($orderId, $amount = null) {
        try {
            $payload = [
                'order_id' => $orderId
            ];
            
            if ($amount) {
                $payload['refund_amount'] = $amount;
            }
            
            $headers = [
                'X-Client-Id: ' . $this->appId,
                'X-Client-Secret: ' . $this->secretKey,
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/orders/' . $orderId . '/refunds');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'refund_id' => $data['refund_id'] ?? '',
                    'refund_amount' => $data['refund_amount'] ?? 0,
                    'refund_status' => $data['refund_status'] ?? ''
                ];
            }
            
            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to process refund'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

?>
