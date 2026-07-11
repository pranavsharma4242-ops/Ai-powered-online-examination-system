<?php
$host = "localhost";
$user = "root";       // or your DB username
$pass = "";           // or your DB password
$db   = "notemarket"; // your database name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
