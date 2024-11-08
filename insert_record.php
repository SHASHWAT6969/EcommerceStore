<?php
include("dbconnection.php");
$con = dbconnection();

// Set Content-Type header if need JSON response else comment
header('Content-Type: application/json');
$email = mysqli_real_escape_string($con, $_POST["email"]);

// Check if the email already exists
$query = "SELECT COUNT(*) AS email_count FROM `users_table` WHERE `user_email` = '$email'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

if ($row['email_count'] > 0) {
    echo json_encode(["success" => "false", "error" => "Email id already exists."]);
} else {
    // Proceed to insert
    $name = mysqli_real_escape_string($con, $_POST["name"]);
    $password = password_hash(mysqli_real_escape_string($con, $_POST["password"]), PASSWORD_BCRYPT);
    
    $sql = "INSERT INTO `users_table` (`user_name`, `user_email`, `user_password`) 
            VALUES ('$name', '$email', '$password')";
    
    if (mysqli_query($con, $sql)) {
        echo json_encode(["success" => "true"]);
    } else {
        echo json_encode(["success" => "false", "error" => mysqli_error($con)]);
    }
}

?>
