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

// Debugging: Check the received POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log(print_r($_POST, true)); // Log the POST data for debugging
}

// Handle API requests
$user_id = $_POST['user_id'] ?? null;
$product_id = $_POST['product_id'] ?? null;
$isLiked = $_POST['isLiked'] ?? null; // Expecting 'true' or 'false'

// Validate input to prevent SQL injection and ensure values are present
if ($user_id === null || $product_id === null || $isLiked === null) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

if (!is_numeric($user_id) || !is_numeric($product_id) || !in_array($isLiked, ['true', 'false'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Prepare SQL statement based on the liked status
if ($isLiked === 'true') {
    $sql = "INSERT INTO likes (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=id"; // If already liked, do nothing
} else {
    $sql = "DELETE FROM likes WHERE user_id = ? AND product_id = ?";
}

$stmt = $con->prepare($sql);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $con->error]);
    exit();
}

// Bind parameters and execute
$stmt->bind_param("ii", $user_id, $product_id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
    exit();
}

// Clean up
$stmt->close();
$con->close();

// Return a success response
echo json_encode(['success' => true]);
?>
