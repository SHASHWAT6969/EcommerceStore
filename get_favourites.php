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

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the action parameter
    $action = $_POST['action'] ?? null;

    // Fetch liked products
    if ($action === 'fetch') {
        $user_id = $_POST['user_id'] ?? null;

        if ($user_id === null || !is_numeric($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit();
        }

        $sql = "SELECT p.product_id, p.name, p.price, p.description, p.image_url, p.MRP, l.quantity
                FROM likes l
                JOIN products p ON l.product_id = p.product_id
                WHERE l.user_id = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        echo json_encode(['success' => true, 'products' => $products]);

    // Update product quantity
    } elseif ($action === 'update') {
        $user_id = $_POST['user_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        if ($user_id === null || !is_numeric($user_id) ||
            $product_id === null || !is_numeric($product_id) ||
            $quantity === null || !is_numeric($quantity)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }

        // Update the quantity in the database
        $sql = "UPDATE likes SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Quantity updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity: ' . $stmt->error]);
        }

        $stmt->close();

    // Remove a product from favorites
    } elseif ($action === 'remove') {
        $user_id = $_POST['user_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;

        if ($user_id === null || !is_numeric($user_id) ||
            $product_id === null || !is_numeric($product_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }

        // Remove the product from the favorites
        $sql = "DELETE FROM likes WHERE user_id = ? AND product_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Product removed from favorites.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found in favorites.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product: ' . $stmt->error]);
        }

        $stmt->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$con->close();
?>
