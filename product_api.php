<?php
include("dbconnection.php");
$con = dbconnection();

// Set Content-Type header for JSON response
header('Content-Type: application/json');

if ($con->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get the category from the query string
    $category = isset($_GET['category']) ? $con->real_escape_string($_GET['category']) : '';

    // Prepare the SQL query to fetch products based on the category
    if ($category) {
        $result = $con->query("SELECT * FROM products WHERE categories = '$category'");
    } else {
        $result = $con->query("SELECT * FROM products");
    }

    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($products);
    } else {
        echo json_encode(['error' => 'Failed to fetch products']);
    }
}

$con->close();
?>
