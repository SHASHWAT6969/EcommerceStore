<?php
include("dbconnection.php");
$con = dbconnection();

header('Content-Type: application/json');

if ($con->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $con->connect_error]);
    exit();
}

// Get user_id from request
$data = json_decode(file_get_contents('php://input'), true);
$userId = isset($data['user_id']) ? $data['user_id'] : null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit();
}

// Prepare statement to fetch orders for the given user ID
$stmt = $con->prepare("
    SELECT o.order_id, o.quantity, o.price, o.order_date, p.product_id, p.name, p.description, p.stock_quantity, p.image_url, p.MRP
    FROM orders o
    JOIN products p ON o.product_id = p.product_id
    WHERE o.user_id = ?
");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode(['success' => true, 'orders' => $orders]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch orders.']);
}

$stmt->close();
$con->close();
?>
