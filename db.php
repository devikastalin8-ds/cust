<?php
// Simple Database Connection File
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_auditorium_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>