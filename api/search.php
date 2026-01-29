<?php
/**
 * Search Products
 */
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

header('Content-Type: application/json');

$query = sanitize($_GET['q'] ?? '');
$results = [];

if (strlen($query) >= 2) {
    $conn = getConnection();
    $searchTerm = '%' . $conn->real_escape_string($query) . '%';
    
    $sql = "SELECT id, name FROM products 
            WHERE status = 'active' 
            AND (name LIKE '$searchTerm' OR description LIKE '$searchTerm')
            LIMIT 10";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    
    $conn->close();
}

echo json_encode(['results' => $results]);
?>
