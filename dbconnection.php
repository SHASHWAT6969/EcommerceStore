<?php
function dbconnection() {
    $con = mysqli_connect("localhost", "root", "", "clothesapp");

    // Check connection
    if (!$con) {
        die(json_encode([
            "success" => "false",
            "error" => "Connection failed: " . mysqli_connect_error()
        ]));
    }

    return $con;
}
