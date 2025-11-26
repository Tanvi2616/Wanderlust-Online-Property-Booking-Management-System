<?php
$servername = "localhost";
$username = "root";
$password = "";  // empty string for XAMPP default
$dbname = "wanderlust";

$conn = new mysqli($servername, $username, $password, $dbname , 3307);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}