<?php
/**
 * Payment Processing API
 * Handles Cashfree payment creation and verification
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

$conn = getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'User not authenticated');
}

$userId = $_SESSION['user_id'];
$cashfree = new CashfreePayment();

// Create payment session
if ($action === 'create_session') {
    try {
        $orderId = sanitize($_POST['order_id'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $customerEmail = sanitize($_POST['email'] ?? '');
        $customerPhone = sanitize($_POST['phone'] ?? '');
        
        if (!$orderId || $amount <= 0 || !$customerEmail || !$customerPhone) {
            jsonResponse(false, 'Invalid payment parameters');
        }
        
        // Verify order exists and belongs to user
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND payment_method = 'cashfree'");
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            jsonResponse(false, 'Invalid order');
        }
        $stmt->close();
        
        // Create payment session
        $result = $cashfree->createPaymentSession(
            'ORD-' . $orderId . '-' . time(),
            $amount,
            $customerEmail,
            $customerPhone
        );
        
        if ($result['success']) {
            // Save payment session
            $cfOrderId = $result['order_id'];
            $stmt = $conn->prepare("UPDATE orders SET cf_order_id = ? WHERE id = ?");
            $stmt->bind_param("si", $cfOrderId, $orderId);
            $stmt->execute();
            $stmt->close();
            
            jsonResponse(true, 'Payment session created', [
                'session_id' => $result['session_id'],
                'payment_link' => $result['payment_link'],
                'order_id' => $cfOrderId
            ]);
        } else {
            jsonResponse(false, $result['error'] ?? 'Failed to create payment session');
        }
    } catch (Exception $e) {
        jsonResponse(false, $e->getMessage());
    }
}

// Verify payment
elseif ($action === 'verify') {
    try {
        $orderId = sanitize($_POST['order_id'] ?? '');
        
        if (!$orderId) {
            jsonResponse(false, 'Order ID required');
        }
        
        // Get CF order ID from database
        $stmt = $conn->prepare("SELECT cf_order_id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if (!$order || !$order['cf_order_id']) {
            jsonResponse(false, 'Order not found');
        }
        
        // Verify payment with Cashfree
        $paymentResult = $cashfree->verifyPayment($order['cf_order_id']);
        
        if ($paymentResult['success']) {
            $paymentStatus = strtolower($paymentResult['payment_status']);
            
            // Update order status
            if ($paymentStatus === 'succeeded' || $paymentStatus === 'paid') {
                $status = 'completed';
                $orderStatus = 'confirmed';
            } else {
                $status = 'failed';
                $orderStatus = 'placed';
            }
            
            $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $orderStatus, $orderId);
            $stmt->execute();
            $stmt->close();
            
            if ($status === 'completed') {
                // Send success email
                $orderData = getOrderData($orderId);
                sendEmail(
                    $orderData['email'],
                    'Payment Received - Order Confirmation',
                    '<h2>Payment Successful!</h2>' .
                    '<p>Your payment has been received and order confirmed.</p>' .
                    '<p>Order Number: <strong>' . $orderData['order_number'] . '</strong></p>'
                );
                
                jsonResponse(true, 'Payment verified successfully', [
                    'status' => 'completed',
                    'order_id' => $orderId
                ]);
            } else {
                jsonResponse(false, 'Payment verification failed', [
                    'status' => $paymentStatus
                ]);
            }
        } else {
            jsonResponse(false, $paymentResult['error'] ?? 'Payment verification failed');
        }
    } catch (Exception $e) {
        jsonResponse(false, $e->getMessage());
    }
}

// Webhook for Cashfree payment updates
elseif ($action === 'webhook') {
    try {
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (!$postData) {
            jsonResponse(false, 'Invalid webhook data');
        }
        
        $orderId = $postData['order_id'] ?? '';
        $eventType = $postData['type'] ?? '';
        $paymentStatus = $postData['payment']['cf_payment_status'] ?? '';
        
        // Extract order ID from CF order ID
        preg_match('/ORD-(\d+)-/', $orderId, $matches);
        $actualOrderId = $matches[1] ?? 0;
        
        if ($actualOrderId && $paymentStatus === 'SUCCESS') {
            // Mark payment as completed
            $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?");
            $status = 'completed';
            $orderStatus = 'confirmed';
            $stmt->bind_param("ssi", $status, $orderStatus, $actualOrderId);
            $stmt->execute();
            $stmt->close();
            
            jsonResponse(true, 'Webhook processed');
        } else {
            jsonResponse(false, 'Unable to process webhook');
        }
    } catch (Exception $e) {
        jsonResponse(false, $e->getMessage());
    }
}

else {
    jsonResponse(false, 'Invalid action');
}

/**
 * Get order data helper
 */
function getOrderData($orderId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT id, order_number, total_amount, shipping_address 
        FROM orders WHERE id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if ($order && $order['shipping_address']) {
        $shipping = json_decode($order['shipping_address'], true);
        $order['email'] = $shipping['email'];
    }
    
    return $order;
}

$conn->close();
?>
