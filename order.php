<?php
include("dbconnection.php");
$con = dbconnection();

header('Content-Type: application/json');

if ($con->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $con->connect_error]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Check if required fields are present
if (!isset($data['user_id']) || !isset($data['products'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

$userId = $data['user_id'];
$orderDate = date('Y-m-d H:i:s');

// Start a transaction
$con->begin_transaction();

try {
    // Prepare statement for inserting order details
    $stmt = $con->prepare("INSERT INTO orders (user_id, product_id, quantity, price, order_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiids", $userId, $productId, $quantity, $price, $orderDate);

    // Insert each product into the orders table
    foreach ($data['products'] as $product) {
        if (isset($product['product_id']) && isset($product['quantity']) && isset($product['price'])) {
            $productId = $product['product_id'];
            $quantity = $product['quantity'];
            $price = $product['price'];

            // Check if product_id exists in the products table
            $checkProductStmt = $con->prepare("SELECT COUNT(*) FROM products WHERE product_id = ?");
            $checkProductStmt->bind_param("i", $productId);
            $checkProductStmt->execute();
            $checkProductStmt->bind_result($count);
            $checkProductStmt->fetch();
            $checkProductStmt->close();

            if ($count > 0) {
                // Product exists, proceed with inserting the order
                $stmt->execute();
            } else {
                // Product doesn't exist in the database, return error
                echo json_encode(['success' => false, 'message' => 'Product with ID ' . $productId . ' does not exist.']);
                $con->rollback();
                exit();
            }
        } else {
            throw new Exception('Product data is incomplete.');
        }
    }

    // Clear the cart (assumed to be in the 'likes' table)
    $clearCartStmt = $con->prepare("DELETE FROM likes WHERE user_id = ?");
    $clearCartStmt->bind_param("i", $userId);
    $clearCartStmt->execute();
    $clearCartStmt->close();

    // Commit the transaction
    $con->commit();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Order placed and cart cleared.']);

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $con->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    // Close statement and connection
    $stmt->close();
    $con->close();
}
?>
