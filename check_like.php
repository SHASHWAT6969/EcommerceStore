<?php
include("dbconnection.php");
$con = dbconnection();

// Set Content-Type header for JSON response
header('Content-Type: application/json');

// Check for a successful database connection
if ($con->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $con->connect_error]);
    exit();
}

// Handle API requests
$user_id = $_POST['user_id'] ?? null;
$product_id = $_POST['product_id'] ?? null;

// Validate input
if ($user_id === null || $product_id === null) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Prepare SQL statement to check if the product is liked
$sql = "SELECT COUNT(*) as count FROM likes WHERE user_id = ? AND product_id = ?";
$stmt = $con->prepare($sql);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $con->error]);
    exit();
}

// Bind parameters and execute
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$stmt->close();
$con->close();

// Return the like status
$isLiked = $row['count'] > 0 ? 'true' : 'false';
echo json_encode(['isLiked' => $isLiked]);
?>
