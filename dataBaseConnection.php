<?php

// Establish a connection to the database
function connection(){
    $dbHost = 'localhost'; // Hostname
    $dbUsername = 'root'; // Database username
    $dbPassword = ''; // Database password
    $dbName = 'telegram_bot'; // Database name
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    return $conn;
// Close the connection
    $conn->close();

}
?>