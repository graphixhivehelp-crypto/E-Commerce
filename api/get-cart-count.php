<?php
/**
 * Get Cart Count
 */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

SessionManager::startSession();

header('Content-Type: application/json');

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
}

echo json_encode(['count' => $count]);
?>
