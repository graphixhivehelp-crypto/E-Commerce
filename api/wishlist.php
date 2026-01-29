<?php
/**
 * Wishlist API
 */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

SessionManager::startSession();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

try {
    if (!isLoggedIn()) {
        throw new Exception('Please login to use wishlist');
    }

    $conn = getConnection();
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'] ?? 'add';
    $productId = intval($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        throw new Exception('Invalid product');
    }

    // Check if product exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        throw new Exception('Product not found');
    }
    $stmt->close();

    if ($action === 'add') {
        // Check if already in wishlist
        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();

        if (!$stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            // Add to wishlist
            $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $productId);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Added to wishlist'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Already in wishlist'];
        }
        $stmt->close();
    } else {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $response = ['success' => true, 'message' => 'Removed from wishlist'];
        }
        $stmt->close();
    }

    $conn->close();
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>
