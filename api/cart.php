<?php
/**
 * Cart API - Add, Update, Remove cart items
 */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

SessionManager::startSession();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    if (!isLoggedIn()) {
        throw new Exception('Please login to use cart');
    }

    $conn = getConnection();
    $userId = $_SESSION['user_id'];

    switch ($action) {
        case 'add':
            $productId = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            $size = sanitize($_POST['size'] ?? '');
            $color = sanitize($_POST['color'] ?? '');

            if ($productId <= 0 || $quantity <= 0) {
                throw new Exception('Invalid product or quantity');
            }

            // Check if product exists
            $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$product) {
                throw new Exception('Product not found');
            }

            if ($product['stock'] < $quantity) {
                throw new Exception('Insufficient stock');
            }

            // Check if item already in cart
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $cartItem = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($cartItem) {
                // Update quantity
                $newQty = $cartItem['quantity'] + $quantity;
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $newQty, $cartItem['id']);
            } else {
                // Add to cart
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, selected_size, selected_color) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiiss", $userId, $productId, $quantity, $size, $color);
            }

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Product added to cart'];
            }
            $stmt->close();
            break;

        case 'remove':
            $itemId = intval($_POST['item_id'] ?? 0);
            if ($itemId <= 0) throw new Exception('Invalid item');

            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $itemId, $userId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Item removed from cart'];
            }
            $stmt->close();
            break;

        case 'update':
            $itemId = intval($_POST['item_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);

            if ($itemId <= 0 || $quantity < 1) {
                throw new Exception('Invalid item or quantity');
            }

            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $itemId, $userId);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Cart updated'];
            }
            $stmt->close();
            break;

        default:
            throw new Exception('Unknown action');
    }

    $conn->close();
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>
