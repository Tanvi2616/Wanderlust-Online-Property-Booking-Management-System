<?php
include 'db_connect.php'; // your DB connection file
session_start();

// Get owner ID and name from session
$host_id = $_SESSION['user_id'] ?? null;
$host_name = $_SESSION['user_name'] ?? 'Unknown Owner';
$role = $_SESSION['role'] ?? null;

// Ensure only owners can add properties
if (!$host_id || $role !== 'owner') {
    echo "<script>alert('Access denied. Only owners can add properties.'); window.location.href='login.html';</script>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $property_type = $_POST['property_type'];
    $price = $_POST['price'];
    $location = $_POST['location'];

    // Handle image upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO properties (name, description, property_type, price, location, host_id, host_name, ratings, reviews, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisss", $name, $description, $property_type, $price, $location, $host_id, $host_name, $target_file);

        if ($stmt->execute()) {
            echo "<script>alert('Property added successfully!'); window.location.href='owner_dashboard.html';</script>";
        } else {
            echo "<script>alert('Database error: Unable to add property.'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error uploading image. Please try again.'); window.history.back();</script>";
    }
}

$conn->close();
?>
