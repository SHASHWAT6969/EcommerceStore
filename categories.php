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
    $result = $con->query("SELECT * FROM categories");
    $products = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($products);
}
$con->close();
?>
