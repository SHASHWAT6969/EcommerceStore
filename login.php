<?php
include("dbconnection.php");
$con = dbconnection();

// Set Content-Type header for JSON response
header('Content-Type: application/json');

// Use POST method to receive data
$email = mysqli_real_escape_string($con, $_POST["email"]);
$password = mysqli_real_escape_string($con, $_POST["password"]);

// Check if the email exists in the database
$query = "SELECT * FROM `users_table` WHERE `user_email` = '$email'";
$result = mysqli_query($con, $query);

if ($result && $result->num_rows > 0) {
    $userRecord = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($password, $userRecord['user_password'])) {
        // Password is correct, login allowed
        echo json_encode(array("success" => "true", "userData" => $userRecord));
    } else {
        // Password is incorrect
        echo json_encode(array("success" => "false", "message" => "Invalid password."));
    }
} else {
    // Email not found in database
    echo json_encode(array("success" => "false", "message" => "Email not found."));
}

// Close the database connection
mysqli_close($con);
?>
