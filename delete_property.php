<?php
include 'db_connect.php';
session_start();

// Verify owner login
$host_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$host_id || $role !== 'owner') {
    echo "<script>
            alert('Access denied. Only owners can delete properties.');
            window.location.href='login.html';
          </script>";
    exit;
}

// Get property ID from URL
$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    echo "<script>
            alert('Invalid property ID.');
            window.location.href='manage_listings.php';
          </script>";
    exit;
}

// Check if property belongs to logged-in owner
$sql = "SELECT image FROM properties WHERE id = ? AND host_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $property_id, $host_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
            alert('Property not found or you do not have permission to delete it.');
            window.location.href='manage_listings.php';
          </script>";
    exit;
}

$property = $result->fetch_assoc();
$imagePath = $property['image'];

// Delete property from the database
$deleteSql = "DELETE FROM properties WHERE id = ? AND host_id = ?";
$stmt = $conn->prepare($deleteSql);
$stmt->bind_param("ii", $property_id, $host_id);

if ($stmt->execute()) {
    // Delete the property image if it exists
    if (!empty($imagePath) && file_exists($imagePath)) {
        unlink($imagePath);
    }

    echo "<script>
            alert('✅ Property deleted successfully!');
            window.location.href='manage_listings.php';
          </script>";
} else {
    echo "<script>
            alert('❌ Error deleting property. Please try again.');
            window.location.href='manage_listings.php';
          </script>";
}

$stmt->close();
$conn->close();
?>
